<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Service;
use App\Models\ServiceStage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ServiceStageKanbanTest extends TestCase
{
    use RefreshDatabase;

    private Admin $admin;
    private Service $service;
    private ServiceStage $stage1;
    private ServiceStage $stage2;
    private ServiceStage $stage3;

    protected function setUp(): void
    {
        parent::setUp();

        Role::create(['name' => 'Super Admin', 'guard_name' => 'admin']);
        $this->admin = Admin::create([
            'name' => 'Admin Manager',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'phone' => '9999999999',
            'role' => 'admin',
            'is_active' => true,
        ]);
        $this->admin->assignRole('Super Admin');

        $this->service = Service::create([
            'name' => 'High Density Trellis Installation',
            'description' => 'Complete concrete and wire trellis setup.',
            'category' => 'Engineering',
            'is_active' => true,
        ]);

        $this->stage1 = $this->service->stages()->create([
            'name' => 'Site Inspection & Layout',
            'sort_order' => 1,
            'requires_photo' => true,
        ]);

        $this->stage2 = $this->service->stages()->create([
            'name' => 'Pole & Anchor Erection',
            'sort_order' => 2,
        ]);

        $this->stage3 = $this->service->stages()->create([
            'name' => 'Wire Stringing & Quality Check',
            'sort_order' => 3,
            'requires_pdf' => true,
        ]);
    }

    public function test_can_view_service_stages_kanban(): void
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.services.stages.index', $this->service));

        $response->assertOk();
        $response->assertSee('Service Stages Workflow');
        $response->assertSee('Kanban Board');
        $response->assertSee('Site Inspection & Layout');
        $response->assertSee('Pole & Anchor Erection');
        $response->assertSee('Wire Stringing & Quality Check');
    }

    public function test_can_reorder_stages_via_drag_and_drop_api(): void
    {
        // Drag Stage 3 to first position, followed by Stage 1, then Stage 2
        $newOrder = [
            $this->stage3->id,
            $this->stage1->id,
            $this->stage2->id,
        ];

        $response = $this->actingAs($this->admin, 'admin')
            ->postJson(route('admin.services.stages.reorder', $this->service), [
                'order' => $newOrder,
            ]);

        $response->assertOk();
        $response->assertJsonPath('success', true);

        // Verify updated sort_order in database
        $this->stage3->refresh();
        $this->stage1->refresh();
        $this->stage2->refresh();

        $this->assertEquals(1, $this->stage3->sort_order);
        $this->assertEquals(2, $this->stage1->sort_order);
        $this->assertEquals(3, $this->stage2->sort_order);
    }
}
