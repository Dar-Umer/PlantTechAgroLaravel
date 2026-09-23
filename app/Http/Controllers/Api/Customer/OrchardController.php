<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Models\Orchard;
use App\Models\WorkOrder;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class OrchardController extends Controller
{
    public function index(Request $request)
    {
        $orchards = Orchard::where('customer_id', $request->user()->id)
            ->withCount('workOrders')
            ->latest()
            ->get();

        return response()->json([
            'orchards' => $orchards->map(fn (Orchard $orchard) => static::summary($orchard))->values(),
            'counts' => [
                'total' => $orchards->count(),
                'company_established' => $orchards->where('is_company_established', true)->count(),
                'self_registered' => $orchards->where('is_company_established', false)->count(),
                'total_kanals' => round($orchards->sum('area_kanals'), 2),
                'total_plants' => $orchards->sum('tree_count'),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:1000'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'area_kanals' => ['required', 'numeric', 'min:0.1', 'max:10000'],
            'tree_count' => ['required', 'integer', 'min:1', 'max:1000000'],
            'date_of_establishment' => ['required', 'date', 'before_or_equal:today'],
            'variety_notes' => ['nullable', 'string', 'max:1000'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $orchard = Orchard::create($data + [
            'customer_id' => $request->user()->id,
            'is_company_established' => false, // Customer added their own orchard
            'status' => 'active',
        ]);

        return response()->json([
            'message' => 'Orchard added successfully.',
            'orchard' => static::summary($orchard),
        ], 201);
    }

    public function show(Request $request, int $id)
    {
        $orchard = Orchard::where('customer_id', $request->user()->id)
            ->with(['workOrders:id,number,service_name,status,orchard_id,created_at,completed_at'])
            ->findOrFail($id);

        $payload = static::summary($orchard);
        $payload['work_orders'] = $orchard->workOrders->map(fn (WorkOrder $wo) => [
            'id' => $wo->id,
            'number' => $wo->number,
            'service_name' => $wo->service_name,
            'status' => $wo->status,
            'status_label' => WorkOrder::STATUSES[$wo->status] ?? $wo->status,
            'created_at' => $wo->created_at?->toISOString(),
            'completed_at' => $wo->completed_at?->toISOString(),
        ])->values();

        return response()->json([
            'orchard' => $payload,
        ]);
    }

    public function update(Request $request, int $id)
    {
        $orchard = Orchard::where('customer_id', $request->user()->id)->findOrFail($id);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:1000'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'area_kanals' => ['required', 'numeric', 'min:0.1', 'max:10000'],
            'tree_count' => ['required', 'integer', 'min:1', 'max:1000000'],
            'date_of_establishment' => ['required', 'date', 'before_or_equal:today'],
            'variety_notes' => ['nullable', 'string', 'max:1000'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $orchard->update($data);

        return response()->json([
            'message' => 'Orchard details updated.',
            'orchard' => static::summary($orchard),
        ]);
    }

    public function destroy(Request $request, int $id)
    {
        $orchard = Orchard::where('customer_id', $request->user()->id)->findOrFail($id);

        if ($orchard->is_company_established) {
            throw ValidationException::withMessages([
                'orchard' => 'Company-established orchards cannot be deleted directly. Contact Plant Tech Agro support.',
            ]);
        }

        if ($orchard->workOrders()->exists()) {
            throw ValidationException::withMessages([
                'orchard' => 'This orchard has service records and cannot be deleted.',
            ]);
        }

        $orchard->delete();

        return response()->json([
            'message' => 'Orchard removed.',
        ]);
    }

    public static function summary(Orchard $orchard): array
    {
        return [
            'id' => $orchard->id,
            'orchard_id' => $orchard->orchard_id,
            'name' => $orchard->name,
            'address' => $orchard->address,
            'latitude' => $orchard->latitude,
            'longitude' => $orchard->longitude,
            'google_maps_url' => $orchard->google_maps_url,
            'area_kanals' => (float) $orchard->area_kanals,
            'tree_count' => (int) $orchard->tree_count,
            'date_of_establishment' => $orchard->date_of_establishment?->toDateString(),
            'age' => $orchard->age,
            'is_company_established' => (bool) $orchard->is_company_established,
            'company_tag' => $orchard->company_tag,
            'variety_notes' => $orchard->variety_notes,
            'status' => $orchard->status,
            'notes' => $orchard->notes,
            'work_orders_count' => $orchard->work_orders_count ?? $orchard->workOrders()->count(),
            'created_at' => $orchard->created_at?->toISOString(),
        ];
    }
}
