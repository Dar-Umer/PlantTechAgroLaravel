<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImageOptimizerService
{
    /**
     * Store and optimize an uploaded file if it is an image.
     * Fallback safely to regular store() if compression is disabled or unsupported.
     */
    public static function store(
        UploadedFile|string $file,
        string $directory = 'uploads',
        string $disk = 'public',
        array $overrides = []
    ): string {
        $settings = static::settings($overrides);

        // If not an UploadedFile, handle path string
        if (is_string($file)) {
            if (! is_file($file)) {
                return $file;
            }
            $sourcePath = $file;
            $mimeType = mime_content_type($file) ?: 'image/jpeg';
            $originalName = basename($file);
            $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION) ?: 'jpg');
        } else {
            $sourcePath = $file->getRealPath();
            $mimeType = $file->getMimeType() ?: $file->getClientMimeType() ?: 'image/jpeg';
            $originalName = $file->getClientOriginalName();
            $extension = strtolower($file->getClientOriginalExtension() ?: 'jpg');
        }

        // If compression is disabled or file is not an image, store standard
        if (! $settings['compression_enabled'] || ! static::isCompressibleImage($mimeType, $extension)) {
            if ($file instanceof UploadedFile) {
                return $file->store($directory, $disk);
            }

            $targetName = Str::random(40) . '.' . $extension;
            $targetPath = trim($directory, '/') . '/' . $targetName;
            Storage::disk($disk)->put($targetPath, file_get_contents($sourcePath));
            return $targetPath;
        }

        try {
            return static::compressAndStore($sourcePath, $directory, $disk, $settings, $extension);
        } catch (\Throwable $e) {
            Log::warning('ImageOptimizerService: Optimization failed, falling back to standard store: ' . $e->getMessage());

            if ($file instanceof UploadedFile) {
                return $file->store($directory, $disk);
            }

            $targetName = Str::random(40) . '.' . $extension;
            $targetPath = trim($directory, '/') . '/' . $targetName;
            Storage::disk($disk)->put($targetPath, file_get_contents($sourcePath));
            return $targetPath;
        }
    }

    /**
     * Perform the actual GD image compression, orientation fix, downscaling, and encoding.
     */
    protected static function compressAndStore(
        string $sourcePath,
        string $directory,
        string $disk,
        array $settings,
        string $originalExtension
    ): string {
        $rawContents = file_get_contents($sourcePath);
        if ($rawContents === false) {
            throw new \RuntimeException('Cannot read source file for compression.');
        }

        $image = @imagecreatefromstring($rawContents);
        if (! $image) {
            throw new \RuntimeException('GD cannot parse image data from source.');
        }

        // 1. Auto-orient from phone camera EXIF data
        if ($settings['auto_orient'] && function_exists('exif_read_data')) {
            try {
                $exif = @exif_read_data($sourcePath);
                if (! empty($exif['Orientation'])) {
                    switch ($exif['Orientation']) {
                        case 3:
                            $rotated = imagerotate($image, 180, 0);
                            imagedestroy($image);
                            $image = $rotated;
                            break;
                        case 6:
                            $rotated = imagerotate($image, -90, 0);
                            imagedestroy($image);
                            $image = $rotated;
                            break;
                        case 8:
                            $rotated = imagerotate($image, 90, 0);
                            imagedestroy($image);
                            $image = $rotated;
                            break;
                    }
                }
            } catch (\Throwable) {
                // Ignore EXIF errors on stripped images
            }
        }

        $width = imagesx($image);
        $height = imagesy($image);
        $maxDim = (int) $settings['max_dimension'];

        // 2. Downscale if dimensions exceed max bounding box
        if ($maxDim > 0 && ($width > $maxDim || $height > $maxDim)) {
            $ratio = min($maxDim / $width, $maxDim / $height);
            $newWidth = (int) max(1, round($width * $ratio));
            $newHeight = (int) max(1, round($height * $ratio));

            $newImage = imagecreatetruecolor($newWidth, $newHeight);

            // Handle transparency for PNG/WebP
            imagealphablending($newImage, false);
            imagesavealpha($newImage, true);
            $transparent = imagecolorallocatealpha($newImage, 255, 255, 255, 127);
            imagefilledrectangle($newImage, 0, 0, $newWidth, $newHeight, $transparent);

            imagecopyresampled($newImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
            imagedestroy($image);
            $image = $newImage;
        } else {
            // Keep alpha transparent channels intact
            imagealphablending($image, false);
            imagesavealpha($image, true);
        }

        // 3. Select target format and save to temporary file
        $tempPath = tempnam(sys_get_temp_dir(), 'pta_opt_');
        $quality = max(10, min(100, (int) $settings['quality']));
        $targetExt = $originalExtension;

        if ($settings['convert_to_webp'] && function_exists('imagewebp')) {
            $targetExt = 'webp';
            imagewebp($image, $tempPath, $quality);
        } elseif (in_array($originalExtension, ['png'], true)) {
            $pngCompression = (int) round((100 - $quality) / 10);
            $pngCompression = max(0, min(9, $pngCompression));
            imagepng($image, $tempPath, $pngCompression);
        } else {
            $targetExt = 'jpg';
            imagejpeg($image, $tempPath, $quality);
        }

        imagedestroy($image);

        // 4. Put into permanent Laravel storage
        $filename = Str::random(40) . '.' . $targetExt;
        $relativePath = trim($directory, '/') . '/' . $filename;

        Storage::disk($disk)->put($relativePath, file_get_contents($tempPath));

        if (file_exists($tempPath)) {
            @unlink($tempPath);
        }

        return $relativePath;
    }

    /**
     * Check if a mime/extension is an image that should be compressed.
     */
    public static function isCompressibleImage(?string $mime, ?string $extension): bool
    {
        $validMimes = [
            'image/jpeg', 'image/jpg', 'image/png', 'image/webp', 'image/bmp', 'image/x-ms-bmp'
        ];

        $validExts = ['jpg', 'jpeg', 'png', 'webp', 'bmp'];

        return in_array(strtolower((string) $mime), $validMimes, true)
            || in_array(strtolower((string) $extension), $validExts, true);
    }

    /**
     * Resolve effective settings merging config, database settings, and runtime overrides.
     */
    public static function settings(array $overrides = []): array
    {
        $dbSettings = Setting::get('media_compression', []);

        $defaults = [
            'compression_enabled' => (bool) config('media.compression_enabled', true),
            'max_dimension' => (int) config('media.max_dimension', 1920),
            'quality' => (int) config('media.quality', 82),
            'convert_to_webp' => (bool) config('media.convert_to_webp', true),
            'auto_orient' => (bool) config('media.auto_orient', true),
        ];

        return array_merge($defaults, $dbSettings, $overrides);
    }

    /**
     * Run a test compression on an uploaded file and return detailed metrics.
     */
    public static function testCompress(UploadedFile $file): array
    {
        $originalSize = $file->getSize();
        $sourcePath = $file->getRealPath();

        $rawContents = file_get_contents($sourcePath);
        $image = @imagecreatefromstring($rawContents);

        if (! $image) {
            throw new \RuntimeException('Cannot decode image file. Please provide a valid JPG, PNG, or WebP photo.');
        }

        $origW = imagesx($image);
        $origH = imagesy($image);
        imagedestroy($image);

        $tempPath = tempnam(sys_get_temp_dir(), 'pta_test_');
        $settings = static::settings();

        $image = imagecreatefromstring($rawContents);
        $width = imagesx($image);
        $height = imagesy($image);
        $maxDim = (int) $settings['max_dimension'];

        if ($maxDim > 0 && ($width > $maxDim || $height > $maxDim)) {
            $ratio = min($maxDim / $width, $maxDim / $height);
            $newWidth = (int) round($width * $ratio);
            $newHeight = (int) round($height * $ratio);
            $newImage = imagecreatetruecolor($newWidth, $newHeight);
            imagealphablending($newImage, false);
            imagesavealpha($newImage, true);
            imagecopyresampled($newImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
            imagedestroy($image);
            $image = $newImage;
        } else {
            $newWidth = $width;
            $newHeight = $height;
        }

        $quality = (int) $settings['quality'];
        $format = 'jpg';

        if ($settings['convert_to_webp'] && function_exists('imagewebp')) {
            imagewebp($image, $tempPath, $quality);
            $format = 'webp';
        } else {
            imagejpeg($image, $tempPath, $quality);
        }

        imagedestroy($image);

        $compressedSize = filesize($tempPath);
        @unlink($tempPath);

        $savedBytes = max(0, $originalSize - $compressedSize);
        $savedPct = $originalSize > 0 ? round(($savedBytes / $originalSize) * 100, 1) : 0;

        return [
            'original_size' => $originalSize,
            'compressed_size' => $compressedSize,
            'saved_bytes' => $savedBytes,
            'saved_percent' => $savedPct,
            'original_dimensions' => "{$origW} × {$origH}",
            'compressed_dimensions' => "{$newWidth} × {$newHeight}",
            'output_format' => strtoupper($format),
        ];
    }
}
