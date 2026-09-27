<?php

namespace App\Support;

class Media
{
    public static function url(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        if (str_starts_with($path, '/')) {
            return asset(ltrim($path, '/'));
        }

        return asset('storage/'.$path);
    }

    public static function exists(?string $path): bool
    {
        if (! $path) {
            return false;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return true;
        }

        $relative = str_starts_with($path, '/') ? ltrim($path, '/') : 'storage/'.$path;

        return is_file(public_path($relative));
    }

    /**
     * Store and automatically compress an uploaded photo using system settings.
     */
    public static function storeImage(mixed $file, string $directory = 'uploads', string $disk = 'public', array $options = []): string
    {
        return \App\Services\ImageOptimizerService::store($file, $directory, $disk, $options);
    }
}
