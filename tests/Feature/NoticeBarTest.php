<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Service;
use Database\Seeders\AdminSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NoticeBarTest extends TestCase
{
    use RefreshDatabase;

    private function actingAdmin(): Admin
    {
        $this->seed([RolesAndPermissionsSeeder::class, AdminSeeder::class]);

        $admin = Admin::where('email', 'admin@pta.com')->first();
        $admin->assignRole('Super Admin');

        return $admin;
    }

    public function test_notice_bar_hidden_by_default(): void
    {
        Service::factory()->create(['name' => 'Book an Orchard']);

        $this->get('/')->assertOk()->assertDontSee('NOTICE', false);
    }

    public function test_admin_can_save_notices_and_they_appear_on_front_page(): void
    {
        $admin = $this->actingAdmin();
        Service::factory()->create(['name' => 'Book an Orchard']);

        $this->actingAs($admin, 'admin')
            ->put(route('admin.frontend.notice.update'), [
                'notice_enabled' => '1',
                'notice_items' => ['Soil testing camp on Monday', '  ', 'Drip subsidy forms open'],
                'notice_speed' => 25,
            ])
            ->assertRedirect(route('admin.frontend.index', ['tab' => 'notice']));

        $this->assertDatabaseHas('settings', ['key' => 'frontend.notice_bar']);

        // Blank entries are dropped.
        $this->assertCount(2, config('frontend.notice_bar.items'));
        $this->assertSame(25, config('frontend.notice_bar.speed'));

        $this->get('/')->assertOk()
            ->assertSee('NOTICE', false)
            ->assertSee('Soil testing camp on Monday', false)
            ->assertSee('Drip subsidy forms open', false)
            ->assertSee('animation-duration: 25s', false);
    }

    public function test_disabled_notice_bar_renders_nothing(): void
    {
        config(['frontend.notice_bar' => ['enabled' => false, 'items' => ['Hidden notice']]]);
        Service::factory()->create(['name' => 'Book an Orchard']);

        $this->get('/')->assertOk()->assertDontSee('Hidden notice', false);
    }

    public function test_frontend_page_has_notice_tab(): void
    {
        $admin = $this->actingAdmin();

        $this->actingAs($admin, 'admin')->get(route('admin.frontend.index'))
            ->assertOk()
            ->assertSee('Notice Bar', false)
            ->assertSee('notice_enabled', false);
    }
}
