<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Customer;
use App\Models\Service;
use App\Models\ServiceStage;
use App\Models\Setting;
use App\Models\Ticket;
use App\Models\WorkOrder;
use App\Models\WorkOrderStage;
use App\Services\ImageOptimizerService;
use App\Support\Media;
use Database\Seeders\AdminSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ImageCompressionTest extends TestCase
{
    use RefreshDatabase;

    private function actingAdmin(): Admin
    {
        $this->seed([\Database\Seeders\RolesAndPermissionsSeeder::class, AdminSeeder::class]);
        $admin = Admin::where('email', 'admin@pta.com')->first();
        $admin->assignRole('Super Admin');
        return $admin;
    }

    private function createCustomer(): Customer
    {
        return Customer::create([
            'name' => 'Ghulam Hassan',
            'phone' => '9419012345',
            'password' => bcrypt('password123'),
            'status' => 'active',
        ]);
    }

    /**
     * Create a realistic test image with colors and dimensions.
     */
    private function createTestImage(int $width = 2400, int $height = 1800, string $filename = 'test.jpg'): UploadedFile
    {
        $tempPath = tempnam(sys_get_temp_dir(), 'test_img_');
        $img = imagecreatetruecolor($width, $height);

        // Fill with gradient to simulate photo complexity
        for ($y = 0; $y < $height; $y += 20) {
            $color = imagecolorallocate($img, ($y * 2) % 255, ($y * 3) % 255, 120);
            imagefilledrectangle($img, 0, $y, $width, $y + 20, $color);
        }

        imagejpeg($img, $tempPath, 100);
        imagedestroy($img);

        return new UploadedFile($tempPath, $filename, 'image/jpeg', null, true);
    }

    public function test_image_optimizer_service_compresses_and_downscales_photo(): void
    {
        Storage::fake('public');

        $file = $this->createTestImage(2400, 1600);
        $originalSize = $file->getSize();

        $storedPath = ImageOptimizerService::store($file, 'test-uploads', 'public', [
            'compression_enabled' => true,
            'max_dimension' => 1200,
            'quality' => 80,
            'convert_to_webp' => true,
        ]);

        $this->assertTrue(Storage::disk('public')->exists($storedPath));
        $this->assertStringEndsWith('.webp', $storedPath);

        $compressedSize = Storage::disk('public')->size($storedPath);
        $this->assertLessThan($originalSize, $compressedSize);

        // Verify dimensions downscaled to max 1200
        $storedContent = Storage::disk('public')->get($storedPath);
        $storedImg = imagecreatefromstring($storedContent);
        $this->assertLessThanOrEqual(1200, imagesx($storedImg));
        $this->assertLessThanOrEqual(1200, imagesy($storedImg));
        imagedestroy($storedImg);
    }

    public function test_image_optimizer_live_test_compress_returns_metrics(): void
    {
        $file = $this->createTestImage(2000, 1500);

        $result = ImageOptimizerService::testCompress($file);

        $this->assertArrayHasKey('original_size', $result);
        $this->assertArrayHasKey('compressed_size', $result);
        $this->assertArrayHasKey('saved_percent', $result);
        $this->assertGreaterThan(0, $result['saved_percent']);
        $this->assertStringContainsString('×', $result['original_dimensions']);
    }

    public function test_admin_can_update_media_compression_settings(): void
    {
        $admin = $this->actingAdmin();

        $response = $this->actingAs($admin, 'admin')->put('/admin/settings', [
            'tab' => 'media',
            'media_compression_enabled' => '1',
            'media_max_dimension' => '1600',
            'media_quality' => '75',
            'media_convert_to_webp' => '1',
            'media_auto_orient' => '1',
        ]);

        $response->assertRedirect('/admin/settings?tab=media');

        $settings = Setting::get('media_compression');
        $this->assertTrue($settings['compression_enabled']);
        $this->assertEquals(1600, $settings['max_dimension']);
        $this->assertEquals(75, $settings['quality']);
        $this->assertTrue($settings['convert_to_webp']);
        $this->assertTrue($settings['auto_orient']);
    }

    public function test_admin_can_test_compression_endpoint(): void
    {
        $admin = $this->actingAdmin();
        $file = $this->createTestImage(1800, 1200, 'sample.jpg');

        $response = $this->actingAs($admin, 'admin')->postJson('/admin/settings/media/test', [
            'test_image' => $file,
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                'original_size',
                'compressed_size',
                'saved_bytes',
                'saved_percent',
                'original_dimensions',
                'compressed_dimensions',
                'output_format',
            ],
        ]);
    }

    public function test_work_order_stage_completion_compresses_uploaded_photos(): void
    {
        Storage::fake('public');

        $admin = $this->actingAdmin();
        $customer = $this->createCustomer();

        $workOrder = WorkOrder::create([
            'customer_id' => $customer->id,
            'customer_name' => $customer->name,
            'service_name' => 'Land Preparation',
            'status' => 'in_progress',
        ]);

        $stage = WorkOrderStage::create([
            'work_order_id' => $workOrder->id,
            'name' => 'Soil Digging',
            'requires_photo' => 1,
            'min_photos' => 1,
            'status' => 'pending',
        ]);

        $photo = $this->createTestImage(2500, 1800, 'stage_photo.jpg');

        $response = $this->actingAs($admin, 'admin')->patch("/admin/work-orders/{$workOrder->id}/stages/{$stage->id}/complete", [
            'notes' => 'Trench excavated properly',
            'photos' => [$photo],
        ]);

        $response->assertRedirect();

        $attachment = $stage->attachments()->first();
        $this->assertNotNull($attachment);
        $this->assertTrue(Storage::disk('public')->exists($attachment->file_path));

        // Stored file is compressed
        $storedContent = Storage::disk('public')->get($attachment->file_path);
        $storedImg = imagecreatefromstring($storedContent);
        $this->assertLessThanOrEqual(1920, imagesx($storedImg));
        imagedestroy($storedImg);
    }

    public function test_customer_mobile_app_ticket_reply_compresses_attached_photo(): void
    {
        Storage::fake('public');

        $customer = $this->createCustomer();
        $ticket = Ticket::create([
            'ticket_number' => 'TCK-2026-9999',
            'customer_id' => $customer->id,
            'subject' => 'Leaf spot issue',
            'category' => 'agronomy',
            'status' => 'open',
            'priority' => 'medium',
        ]);

        $photo = $this->createTestImage(2400, 1600, 'diseased_leaf.jpg');

        $response = $this->actingAs($customer, 'sanctum')->post('/api/tickets/' . $ticket->id . '/reply', [
            'message' => 'Leaves have yellow spots on lower branches',
            'image' => $photo,
        ]);

        $response->assertStatus(200);

        $attachment = $ticket->attachments()->first();
        $this->assertNotNull($attachment);
        $this->assertTrue(Storage::disk('public')->exists($attachment->file_path));

        $storedContent = Storage::disk('public')->get($attachment->file_path);
        $storedImg = imagecreatefromstring($storedContent);
        $this->assertLessThanOrEqual(1920, imagesx($storedImg));
        imagedestroy($storedImg);
    }
}
