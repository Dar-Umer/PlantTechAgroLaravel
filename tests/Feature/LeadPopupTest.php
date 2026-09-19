<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Lead;
use App\Notifications\NewLeadAlert;
use Database\Seeders\AdminSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeadPopupTest extends TestCase
{
    use RefreshDatabase;

    private function actingAdmin(): Admin
    {
        $this->seed([RolesAndPermissionsSeeder::class, AdminSeeder::class]);

        $admin = Admin::where('email', 'admin@pta.com')->first();
        $admin->assignRole('Super Admin');

        return $admin;
    }

    public function test_latest_endpoint_returns_unread_lead_alerts(): void
    {
        $admin = $this->actingAdmin();
        $lead = Lead::create(['name' => 'Farooq', 'phone' => '9999999999']);
        $admin->notify(new NewLeadAlert($lead));

        $this->actingAs($admin, 'admin')->getJson(route('admin.notifications.latest'))
            ->assertOk()
            ->assertJsonPath('leads.0.lead_id', $lead->id)
            ->assertJsonPath('leads.0.name', 'Farooq')
            ->assertJsonPath('leads.0.phone', '9999999999')
            ->assertJsonPath('popup_enabled', true)
            ->assertJsonStructure(['unread_count', 'poll_interval', 'leads']);
    }

    public function test_read_endpoint_stops_repeat_popups(): void
    {
        $admin = $this->actingAdmin();
        $lead = Lead::create(['name' => 'Farooq', 'phone' => '9999999999']);
        $admin->notify(new NewLeadAlert($lead));

        $notificationId = $admin->unreadNotifications()->first()->id;

        $this->actingAs($admin, 'admin')
            ->postJson(route('admin.notifications.read', $notificationId))
            ->assertOk();

        // Gone from the poller — never pops again.
        $this->actingAs($admin, 'admin')->getJson(route('admin.notifications.latest'))
            ->assertOk()
            ->assertJsonPath('leads', []);
    }

    public function test_read_endpoint_is_scoped_to_own_notifications(): void
    {
        $admin = $this->actingAdmin();
        $other = Admin::create([
            'name' => 'Other', 'email' => 'other@pta.com',
            'password' => 'secret123', 'is_active' => true,
        ]);
        $lead = Lead::create(['name' => 'Farooq', 'phone' => '9999999999']);
        $other->notify(new NewLeadAlert($lead));

        $otherNotificationId = $other->unreadNotifications()->first()->id;

        $this->actingAs($admin, 'admin')
            ->postJson(route('admin.notifications.read', $otherNotificationId))
            ->assertNotFound();
    }

    public function test_latest_endpoint_empty_without_alerts(): void
    {
        $admin = $this->actingAdmin();

        $this->actingAs($admin, 'admin')->getJson(route('admin.notifications.latest'))
            ->assertOk()
            ->assertJsonPath('leads', []);
    }

    public function test_latest_endpoint_requires_admin_auth(): void
    {
        $this->getJson(route('admin.notifications.latest'))->assertRedirect(route('admin.login'));
    }

    public function test_automation_settings_save_popup_controls(): void
    {
        $admin = $this->actingAdmin();

        $this->actingAs($admin, 'admin')->put(route('admin.automation.update'), [
            'channel' => 'both',
            'new_lead_popup_enabled' => '1',
            'new_lead_popup_interval' => 45,
        ])->assertRedirect(route('admin.automation.index'));

        $this->assertTrue((bool) config('automation.new_lead_popup_enabled'));
        $this->assertSame(45, config('automation.new_lead_popup_interval'));
    }

    public function test_admin_layout_contains_popup_poller(): void
    {
        $admin = $this->actingAdmin();
        Lead::create(['name' => 'Farooq', 'phone' => '9999999999']);

        $this->actingAs($admin, 'admin')->get('/admin/leads')
            ->assertOk()
            ->assertSee('leadPopup', false)
            ->assertSee('New Lead Received', false)
            ->assertSee(route('admin.notifications.latest'), false);
    }
}
