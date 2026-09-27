<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Customer;
use App\Models\DeviceToken;
use App\Models\Service;
use App\Models\ServiceStage;
use App\Models\Ticket;
use App\Models\TicketMessage;
use App\Models\WorkOrder;
use App\Models\WorkOrderStage;
use App\Notifications\Channels\FcmChannel;
use App\Notifications\TicketStaffReplyNotification;
use App\Notifications\WorkOrderCompletedNotification;
use App\Notifications\WorkOrderStageCompletedNotification;
use App\Services\FirebaseService;
use Database\Seeders\AdminSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FirebaseNotificationTest extends TestCase
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

    public function test_customer_can_register_device_token(): void
    {
        $customer = $this->createCustomer();

        $response = $this->actingAs($customer, 'sanctum')->postJson('/api/device-tokens', [
            'token' => 'fcm_sample_token_xyz_123',
            'platform' => 'android',
            'device_name' => 'Redmi Note 12',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'message',
            'device_token' => ['id', 'platform', 'device_name', 'last_active_at'],
        ]);

        $this->assertDatabaseHas('device_tokens', [
            'tokenable_type' => Customer::class,
            'tokenable_id' => $customer->id,
            'token' => 'fcm_sample_token_xyz_123',
            'platform' => 'android',
            'device_name' => 'Redmi Note 12',
        ]);
    }

    public function test_registering_same_token_reassigns_or_updates(): void
    {
        $customer1 = $this->createCustomer();
        $customer2 = Customer::create([
            'name' => 'Tariq Ahmad',
            'phone' => '9419012346',
            'password' => bcrypt('password123'),
            'status' => 'active',
        ]);

        // First registered to customer 1
        $this->actingAs($customer1, 'sanctum')->postJson('/api/device-tokens', [
            'token' => 'shared_device_token_777',
            'platform' => 'android',
        ])->assertStatus(200);

        // Later logged in by customer 2 on same device
        $this->actingAs($customer2, 'sanctum')->postJson('/api/device-tokens', [
            'token' => 'shared_device_token_777',
            'platform' => 'android',
            'device_name' => 'Pixel 8',
        ])->assertStatus(200);

        $this->assertEquals(1, DeviceToken::where('token', 'shared_device_token_777')->count());
        $this->assertEquals($customer2->id, DeviceToken::where('token', 'shared_device_token_777')->first()->tokenable_id);
    }

    public function test_customer_can_unregister_device_token(): void
    {
        $customer = $this->createCustomer();

        $customer->deviceTokens()->create([
            'token' => 'token_to_remove_888',
            'platform' => 'ios',
        ]);

        $response = $this->actingAs($customer, 'sanctum')->deleteJson('/api/device-tokens', [
            'token' => 'token_to_remove_888',
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseMissing('device_tokens', [
            'token' => 'token_to_remove_888',
        ]);
    }

    public function test_guest_cannot_register_device_token(): void
    {
        $response = $this->postJson('/api/device-tokens', [
            'token' => 'unauth_token',
        ]);

        $response->assertStatus(401);
    }

    public function test_customer_route_notification_for_fcm_returns_active_tokens(): void
    {
        $customer = $this->createCustomer();
        $customer->deviceTokens()->create([
            'token' => 'token_alpha',
            'platform' => 'android',
        ]);
        $customer->deviceTokens()->create([
            'token' => 'token_beta',
            'platform' => 'android',
        ]);

        $tokens = $customer->routeNotificationForFcm();
        $this->assertCount(2, $tokens);
        $this->assertContains('token_alpha', $tokens);
        $this->assertContains('token_beta', $tokens);
    }

    public function test_admin_mobile_app_push_tab_loads_and_displays_stats(): void
    {
        $admin = $this->actingAdmin();
        $customer = $this->createCustomer();
        $customer->deviceTokens()->create([
            'token' => 'token_sample_screen',
            'platform' => 'android',
            'device_name' => 'Samsung S22',
        ]);

        $response = $this->actingAs($admin, 'admin')->get('/admin/mobile-apps?tab=push');
        $response->assertStatus(200);
        $response->assertSee('Push Notifications (FCM)');
        $response->assertSee('Firebase Cloud Messaging');
        $response->assertSee('Total Devices');
        $response->assertSee('Samsung S22');
        $response->assertSee($customer->name);
    }

    public function test_fcm_channel_handles_delivery_gracefully(): void
    {
        $customer = $this->createCustomer();
        $customer->deviceTokens()->create([
            'token' => 'token_delivery_test',
            'platform' => 'android',
        ]);

        $ticket = Ticket::create([
            'ticket_number' => 'TCK-2026-0001',
            'customer_id' => $customer->id,
            'subject' => 'Root rot question',
            'category' => 'agronomy',
            'status' => 'open',
            'priority' => 'medium',
        ]);

        $message = TicketMessage::create([
            'ticket_id' => $ticket->id,
            'sender_type' => Admin::class,
            'sender_id' => $this->actingAdmin()->id,
            'message' => 'Please apply aliette drenching immediately.',
        ]);

        $notification = new TicketStaffReplyNotification($ticket, $message);
        $fcmPayload = $notification->toFcm($customer);

        $this->assertEquals('Support reply on #TCK-2026-0001', $fcmPayload['title']);
        $this->assertStringContainsString('aliette drenching', $fcmPayload['body']);
        $this->assertEquals('OPEN_TICKET', $fcmPayload['data']['click_action']);

        // Test FcmChannel execution (with mock or unconfigured credentials, should not throw)
        $channel = app(FcmChannel::class);
        $channel->send($customer, $notification);
        $this->assertTrue(true);
    }

    public function test_service_stage_can_be_configured_with_push_notification_template(): void
    {
        $admin = $this->actingAdmin();
        $service = Service::create(['name' => 'High Density Plantation', 'slug' => 'hdp']);

        $response = $this->actingAs($admin, 'admin')->post("/admin/services/{$service->id}/stages", [
            'name' => 'Drip Irrigation Installation',
            'description' => 'Laying main line and lateral drippers',
            'sort_order' => 1,
            'requires_photo' => 1,
            'min_photos' => 2,
            'notify_customer' => 1,
            'notification_title' => 'Stage Done: {stage_name}',
            'notification_body' => 'Irrigation installation completed on work order #{work_order_number}.',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('service_stages', [
            'service_id' => $service->id,
            'name' => 'Drip Irrigation Installation',
            'notify_customer' => 1,
            'notification_title' => 'Stage Done: {stage_name}',
            'notification_body' => 'Irrigation installation completed on work order #{work_order_number}.',
        ]);
    }

    public function test_work_order_creation_clones_stage_push_notification_settings(): void
    {
        $admin = $this->actingAdmin();
        $service = Service::create(['name' => 'Canopy Training', 'slug' => 'canopy']);
        $template = ServiceStage::create([
            'service_id' => $service->id,
            'name' => 'Pruning & Bamboo Staking',
            'notify_customer' => 1,
            'notification_title' => 'Staking Completed for {stage_name}',
            'notification_body' => 'All bamboo stakes installed properly.',
        ]);

        $customer = $this->createCustomer();

        $response = $this->actingAs($admin, 'admin')->post('/admin/work-orders', [
            'customer_id' => $customer->id,
            'service_id' => $service->id,
        ]);

        $response->assertRedirect();

        $workOrder = WorkOrder::where('customer_id', $customer->id)->firstOrFail();
        $stage = $workOrder->stages()->firstOrFail();

        $this->assertEquals(1, $stage->notify_customer);
        $this->assertEquals('Staking Completed for {stage_name}', $stage->notification_title);
        $this->assertEquals('All bamboo stakes installed properly.', $stage->notification_body);
    }

    public function test_completing_stage_sends_firebase_push_notification_with_photo(): void
    {
        Notification::fake();
        Storage::fake('public');

        $admin = $this->actingAdmin();
        $customer = $this->createCustomer();
        $service = Service::create(['name' => 'Soil Preparation', 'slug' => 'soil-prep']);
        $template = ServiceStage::create([
            'service_id' => $service->id,
            'name' => 'Deep Ploughing',
            'requires_photo' => 1,
            'min_photos' => 1,
            'notify_customer' => 1,
            'notification_title' => 'Ploughing Done: {stage_name}',
            'notification_body' => 'Ploughing completed for #{work_order_number}.',
        ]);

        $workOrder = WorkOrder::create([
            'customer_id' => $customer->id,
            'customer_name' => $customer->name,
            'service_id' => $service->id,
            'service_name' => $service->name,
            'status' => 'in_progress',
        ]);

        $stage = WorkOrderStage::create([
            'work_order_id' => $workOrder->id,
            'service_stage_id' => $template->id,
            'name' => $template->name,
            'requires_photo' => 1,
            'min_photos' => 1,
            'notify_customer' => 1,
            'notification_title' => $template->notification_title,
            'notification_body' => $template->notification_body,
            'status' => 'pending',
        ]);

        $photo = UploadedFile::fake()->image('ploughing.jpg');

        $response = $this->actingAs($admin, 'admin')->patch("/admin/work-orders/{$workOrder->id}/stages/{$stage->id}/complete", [
            'notes' => 'Deep tilled to 45cm depth with tractor',
            'notify_customer' => '1',
            'photos' => [$photo],
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        Notification::assertSentTo(
            $customer,
            WorkOrderStageCompletedNotification::class,
            function ($notification) use ($stage, $customer) {
                $fcm = $notification->toFcm($customer);
                return $fcm['title'] === "Ploughing Done: {$stage->name}"
                    && str_contains($fcm['body'], 'Ploughing completed for #')
                    && !empty($fcm['image'])
                    && $fcm['data']['type'] === 'work_order_stage_completed';
            }
        );

        // Also check WorkOrderCompletedNotification since this was the only stage
        Notification::assertSentTo(
            $customer,
            WorkOrderCompletedNotification::class
        );
    }

    public function test_completing_stage_can_suppress_notification_when_unchecked(): void
    {
        Notification::fake();

        $admin = $this->actingAdmin();
        $customer = $this->createCustomer();
        $workOrder = WorkOrder::create([
            'customer_id' => $customer->id,
            'customer_name' => $customer->name,
            'service_name' => 'General Orchard Maintenance',
            'status' => 'in_progress',
        ]);

        $stage = WorkOrderStage::create([
            'work_order_id' => $workOrder->id,
            'name' => 'Internal Audit Stage',
            'status' => 'pending',
            'notify_customer' => 1,
        ]);

        // Explicitly uncheck notify_customer
        $this->actingAs($admin, 'admin')->patch("/admin/work-orders/{$workOrder->id}/stages/{$stage->id}/complete", [
            'notes' => 'Internal review only',
            'notify_customer' => '0',
        ])->assertRedirect();

        Notification::assertNotSentTo(
            $customer,
            WorkOrderStageCompletedNotification::class
        );
    }

    public function test_admin_can_view_notification_templates_center(): void
    {
        $admin = $this->actingAdmin();
        $service = Service::create(['name' => 'Canopy Management', 'slug' => 'canopy-mgmt']);
        ServiceStage::create([
            'service_id' => $service->id,
            'name' => 'Pruning Cut',
            'notify_customer' => 1,
        ]);

        $response = $this->actingAs($admin, 'admin')->get('/admin/notification-templates');
        $response->assertStatus(200);
        $response->assertSee('Notification Templates');
        $response->assertSee('Service Stage Templates');
        $response->assertSee('Canopy Management');
        $response->assertSee('Pruning Cut');
    }

    public function test_admin_can_update_system_notification_templates(): void
    {
        $admin = $this->actingAdmin();

        $response = $this->actingAs($admin, 'admin')->put('/admin/notification-templates/system', [
            'templates' => [
                'work_order_completed' => [
                    'title' => 'Woohoo! Order #{work_order_number} Finished!',
                    'body' => 'Your apple orchard service {service_name} has concluded.',
                    'fcm_enabled' => '1',
                ],
            ],
        ]);

        $response->assertRedirect();
        $saved = \App\Models\Setting::get('notification_templates');
        $this->assertEquals('Woohoo! Order #{work_order_number} Finished!', $saved['work_order_completed']['title']);
        $this->assertEquals('Your apple orchard service {service_name} has concluded.', $saved['work_order_completed']['body']);
    }

    public function test_admin_can_update_stage_template_from_notification_templates_center(): void
    {
        $admin = $this->actingAdmin();
        $service = Service::create(['name' => 'Irrigation Setup', 'slug' => 'irr-setup']);
        $stage = ServiceStage::create([
            'service_id' => $service->id,
            'name' => 'Pressure Testing',
            'notify_customer' => 0,
        ]);

        $response = $this->actingAs($admin, 'admin')->put("/admin/notification-templates/stage/{$stage->id}", [
            'notify_customer' => '1',
            'notification_title' => 'Pressure Test Passed: {stage_name}',
            'notification_body' => 'Pressure holds steady at 2.5 bar for {work_order_number}.',
        ]);

        $response->assertRedirect();
        $stage->refresh();
        $this->assertTrue($stage->notify_customer);
        $this->assertEquals('Pressure Test Passed: {stage_name}', $stage->notification_title);
        $this->assertEquals('Pressure holds steady at 2.5 bar for {work_order_number}.', $stage->notification_body);
    }
}
