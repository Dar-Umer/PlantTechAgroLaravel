<?php

namespace App\Services;

use App\Models\Orchard;
use App\Models\WorkOrder;
use Illuminate\Support\Facades\Log;

class OrchardService
{
    /**
     * Create an orchard from a completed work order if applicable and not already linked.
     */
    public function createFromWorkOrder(WorkOrder $workOrder): ?Orchard
    {
        // If already linked to an existing orchard, do not create duplicate
        if ($workOrder->orchard_id) {
            return $workOrder->orchard;
        }

        // Check if an orchard was already generated from this work order
        $existing = Orchard::where('work_order_id', $workOrder->id)->first();
        if ($existing) {
            if (! $workOrder->orchard_id) {
                $workOrder->update(['orchard_id' => $existing->id]);
            }

            return $existing;
        }

        // Determine if this service qualifies for automated orchard establishment
        $service = $workOrder->service;
        $shouldCreate = $service && (
            $service->creates_orchard_on_completion
            || str_contains(strtolower($service->name), 'orchard')
            || str_contains(strtolower($service->slug ?? ''), 'orchard')
        );

        if (! $shouldCreate) {
            return null;
        }

        $customer = $workOrder->customer;
        if (! $customer) {
            return null;
        }

        // Attempt to derive tree count from stage products
        $treeCount = 0;
        if ($workOrder->relationLoaded('stages')) {
            foreach ($workOrder->stages as $stage) {
                if ($stage->relationLoaded('products')) {
                    foreach ($stage->products as $p) {
                        $pName = strtolower($p->name);
                        $unit = strtolower($p->unit ?? '');
                        if (str_contains($pName, 'plant') || str_contains($pName, 'tree') || str_contains($unit, 'plant') || str_contains($unit, 'tree')) {
                            $treeCount += (int) $p->quantity;
                        }
                    }
                }
            }
        }

        $areaKanals = 5.0; // Standard default block size
        $orchardName = $customer->name . ' High Density Orchard';

        // If the customer already has orchards, give it a distinctive index (e.g. #2)
        $existingCount = Orchard::where('customer_id', $customer->id)->count();
        if ($existingCount > 0) {
            $orchardName .= ' #' . ($existingCount + 1);
        }

        $orchard = Orchard::create([
            'customer_id' => $customer->id,
            'work_order_id' => $workOrder->id,
            'name' => $orchardName,
            'address' => $customer->address ?: ($customer->area ?: 'Kashmir, J&K'),
            'area_kanals' => $areaKanals,
            'tree_count' => $treeCount > 0 ? $treeCount : 500,
            'date_of_establishment' => $workOrder->completed_at ? $workOrder->completed_at->toDateString() : now()->toDateString(),
            'is_company_established' => true,
            'status' => 'active',
            'notes' => 'Established by Plant Tech Agro on completion of Work Order ' . $workOrder->number . ' (' . $workOrder->service_name . ').',
        ]);

        $workOrder->update(['orchard_id' => $orchard->id]);

        Log::info("Orchard {$orchard->orchard_id} automatically established for Customer {$customer->orchardist_id} via Work Order {$workOrder->number}.");

        return $orchard;
    }
}
