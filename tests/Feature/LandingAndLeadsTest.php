<?php

namespace Tests\Feature;

use App\Models\Lead;
use App\Models\LeadFormField;
use App\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LandingAndLeadsTest extends TestCase
{
    use RefreshDatabase;

    public function test_landing_page_renders(): void
    {
        Service::factory()->create(['name' => 'Book an Orchard']);

        $this->get('/')
            ->assertOk()
            ->assertSee('Book Now')
            ->assertSee('Book an Orchard');
    }

    public function test_lead_can_be_submitted_from_landing_form(): void
    {
        $service = Service::factory()->create();
        LeadFormField::create([
            'label' => 'Address', 'name' => 'address', 'type' => 'text',
            'is_active' => true, 'sort_order' => 1,
        ]);
        LeadFormField::create([
            'label' => 'Area', 'name' => 'area', 'type' => 'text',
            'is_active' => true, 'sort_order' => 2,
        ]);

        $response = $this->post('/leads', [
            'name' => 'Farooq Ahmad',
            'phone' => '9999999999',
            'service_id' => $service->id,
            'custom' => [
                'address' => 'Pulwama',
                'area' => 'Tahab',
            ],
        ]);

        $response->assertRedirect('/?submitted=1');

        $lead = Lead::first();
        $this->assertNotNull($lead);
        $this->assertEquals('Farooq Ahmad', $lead->name);
        $this->assertEquals('new', $lead->status);
        $this->assertEquals('landing', $lead->source);
        $this->assertEquals('Pulwama', $lead->custom_fields['address']);
        $this->assertEquals('Tahab', $lead->custom_fields['area']);
    }

    public function test_unknown_custom_inputs_are_not_stored(): void
    {
        $service = Service::factory()->create();

        $this->post('/leads', [
            'name' => 'Test',
            'phone' => '9999999999',
            'service_id' => $service->id,
            'custom' => ['hacker_field' => 'x'],
        ])->assertRedirect('/?submitted=1');

        $lead = Lead::first();
        $this->assertEmpty($lead->custom_fields);
    }

    public function test_lead_requires_mandatory_fields(): void
    {
        $this->post('/leads', [])->assertInvalid(['name', 'phone', 'service_id']);
        $this->assertSame(0, Lead::count());
    }

    public function test_lead_phone_must_be_valid(): void
    {
        $service = Service::factory()->create();

        $this->post('/leads', [
            'name' => 'Test',
            'phone' => 'not-a-phone',
            'service_id' => $service->id,
        ])->assertInvalid(['phone']);

        $this->assertSame(0, Lead::count());
    }

    public function test_lead_custom_select_field_validates_options(): void
    {
        $service = Service::factory()->create();
        LeadFormField::create([
            'label' => 'Area',
            'name' => 'area',
            'type' => 'select',
            'options' => ['Pulwama', 'Srinagar'],
            'is_required' => true,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $this->post('/leads', [
            'name' => 'Test',
            'phone' => '9999999999',
            'service_id' => $service->id,
            'custom' => ['area' => 'Invalid Place'],
        ])->assertInvalid(['custom.area']);

        $this->post('/leads', [
            'name' => 'Test',
            'phone' => '9999999999',
            'service_id' => $service->id,
            'custom' => ['area' => 'Pulwama'],
        ])->assertRedirect('/?submitted=1');

        $this->assertSame('Pulwama', Lead::first()->custom_fields['area']);
    }

    public function test_honeypot_submissions_are_ignored(): void
    {
        $service = Service::factory()->create();

        $this->post('/leads', [
            'name' => 'Bot',
            'phone' => '1234567890',
            'service_id' => $service->id,
            'website' => 'spam-link',
        ])->assertRedirect('/?submitted=1');

        $this->assertSame(0, Lead::count());
    }

    public function test_lead_submission_is_rate_limited(): void
    {
        $service = Service::factory()->create();

        $payload = ['name' => 'Test', 'phone' => '9999999999', 'service_id' => $service->id];

        for ($i = 0; $i < 5; $i++) {
            $this->post('/leads', $payload + ['phone' => '999999999'.$i]);
        }

        $this->post('/leads', $payload)->assertStatus(429);

        $this->assertSame(5, Lead::count());
    }
}
