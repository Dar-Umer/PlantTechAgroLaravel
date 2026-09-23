<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Lead;
use App\Models\Orchard;
use App\Models\Service;
use App\Notifications\NewLeadAlert;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ServiceRequestController extends Controller
{
    /**
     * Submit a service inquiry / booking request from the customer mobile app.
     * Differentiates between New Orchard Establishment and Existing Orchard Services.
     */
    public function store(Request $request)
    {
        $customer = $request->user();

        $data = $request->validate([
            'service_id' => ['required', 'integer', Rule::exists('services', 'id')],
            'orchard_id' => ['nullable', 'integer', Rule::exists('orchards', 'id')->where('customer_id', $customer->id)],
            'area_kanals' => ['nullable', 'numeric', 'min:0.5', 'max:500'],
            'location' => ['nullable', 'string', 'max:150'],
            'variety' => ['nullable', 'string', 'max:150'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $service = Service::findOrFail($data['service_id']);

        if (! $service->is_active) {
            throw ValidationException::withMessages([
                'service_id' => 'This service is currently unavailable for booking.',
            ]);
        }

        $orchard = null;
        $customFields = [
            'orchardist_id' => $customer->orchardist_id,
            'service_name' => $service->name,
            'service_category' => $service->category,
            'area' => $customer->area ?? $customer->address,
        ];

        if ($service->creates_orchard_on_completion) {
            // New Orchard Establishment / High Density Booking
            $customFields['service_type'] = 'new_orchard_establishment';
            if (! empty($data['area_kanals'])) {
                $customFields['proposed_area_kanals'] = (float) $data['area_kanals'];
            }
            if (! empty($data['location'])) {
                $customFields['proposed_location'] = $data['location'];
            }
            if (! empty($data['variety'])) {
                $customFields['preferred_variety'] = $data['variety'];
            }
        } else {
            // Service on an Existing Orchard
            $customFields['service_type'] = 'existing_orchard_service';
            if (! empty($data['orchard_id'])) {
                $orchard = Orchard::where('customer_id', $customer->id)->find($data['orchard_id']);
                if ($orchard) {
                    $customFields['orchard_id'] = $orchard->id;
                    $customFields['orchard_name'] = $orchard->name;
                    $customFields['orchard_kanals'] = $orchard->area_kanals;
                }
            }
        }

        $lead = Lead::create([
            'name' => $customer->name,
            'phone' => $customer->phone,
            'service_id' => $service->id,
            'status' => 'new',
            'source' => 'customer_app',
            'converted_customer_id' => $customer->id,
            'notes' => $data['notes'] ?? null,
            'custom_fields' => array_filter($customFields),
        ]);

        // Notify active admins of the new booking lead
        try {
            Admin::where('is_active', true)->get()->each(function ($admin) use ($lead) {
                $admin->notify(new NewLeadAlert($lead));
            });
        } catch (\Throwable $e) {
            // Continue even if notification mail/queue encounters minor issue
        }

        return response()->json([
            'message' => 'Your service request has been received. Our agricultural team will review your requirements and provide an itemized estimate / quotation shortly.',
            'request' => [
                'id' => $lead->id,
                'service_name' => $service->name,
                'service_type' => $service->creates_orchard_on_completion ? 'new_orchard_establishment' : 'existing_orchard_service',
                'status' => 'under_review',
                'target_orchard' => $orchard?->name,
                'proposed_area_kanals' => $data['area_kanals'] ?? null,
                'created_at' => $lead->created_at->toISOString(),
            ],
        ], 201);
    }
}
