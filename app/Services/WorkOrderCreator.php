<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Lead;
use App\Models\Service;
use App\Models\WorkOrder;
use App\Models\WorkOrderStage;
use App\Models\WorkOrderStageProduct;

/**
 * Creates work orders from a service template.
 *
 * Used by lead conversion only: stages and material rows are cloned as
 * snapshots, but NO stock is allocated — materials stay untouched until
 * staff process the order. (Manual creation in Admin/Api WorkOrderController
 * keeps deducting stock as before.)
 */
class WorkOrderCreator
{
    /**
     * @throws \RuntimeException when the lead has no usable service.
     */
    public static function fromConversion(Lead $lead, Customer $customer, ?int $adminId = null): WorkOrder
    {
        $service = $lead->service;

        if (! $service instanceof Service || ! $service->is_active) {
            throw new \RuntimeException('Conversion requires an active service on the lead.');
        }

        $service->loadMissing('stages.products.product');

        $notes = trim(implode("\n\n", array_filter([
            $lead->notes ? "Lead notes: {$lead->notes}" : null,
            static::customFieldsSummary($lead),
            "Auto-created on conversion of lead #{$lead->id} ({$lead->name}, {$lead->phone}).",
        ])));

        $workOrder = WorkOrder::create([
            'customer_id' => $customer->id,
            'customer_name' => $customer->name,
            'service_id' => $service->id,
            'service_name' => $service->name,
            'assigned_agent_id' => null,
            'status' => 'pending',
            'notes' => $notes !== '' ? $notes : null,
            'created_by' => $adminId,
        ]);

        foreach ($service->stages->sortBy('sort_order') as $template) {
            $stage = WorkOrderStage::create([
                'work_order_id' => $workOrder->id,
                'service_stage_id' => $template->id,
                'name' => $template->name,
                'description' => $template->description,
                'sort_order' => $template->sort_order,
                'requires_photo' => $template->requires_photo,
                'min_photos' => $template->min_photos,
                'requires_pdf' => $template->requires_pdf,
            ]);

            foreach ($template->products as $templateProduct) {
                $product = $templateProduct->product;

                if (! $product) {
                    continue;
                }

                WorkOrderStageProduct::create([
                    'work_order_stage_id' => $stage->id,
                    'product_id' => $product->id,
                    'name' => $product->name,
                    'unit' => $product->unit,
                    'quantity' => (float) $templateProduct->quantity,
                    'rate' => (float) $product->rate,
                    'gst_rate' => (float) $product->gst_rate,
                ]);

                // Deliberately no StockService::record() here — see class docblock.
            }
        }

        return $workOrder;
    }

    private static function customFieldsSummary(Lead $lead): ?string
    {
        $fields = $lead->custom_fields;

        if (! is_array($fields) || $fields === []) {
            return null;
        }

        $parts = [];
        foreach ($fields as $key => $value) {
            if ($value === null || $value === '') {
                continue;
            }
            $parts[] = ucfirst(str_replace('_', ' ', (string) $key)) . ': ' . (is_scalar($value) ? (string) $value : json_encode($value));
        }

        return $parts === [] ? null : 'Submitted details: ' . implode(' | ', $parts);
    }
}
