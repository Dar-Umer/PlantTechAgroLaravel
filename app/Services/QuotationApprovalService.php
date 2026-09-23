<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Quotation;
use App\Models\WorkOrder;
use App\Models\WorkOrderStage;
use App\Models\WorkOrderStageProduct;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class QuotationApprovalService
{
    /**
     * Approve quotation and create active Work Order.
     * Can be invoked by Admin on Web or by Customer on Mobile App.
     */
    public static function approveAndStartWork(
        Quotation $quotation,
        ?int $createdByAdminId = null,
        ?Customer $approvingCustomer = null
    ): WorkOrder {
        if (! $quotation->canApprove()) {
            throw new \DomainException('This quotation has already been approved or linked to a work order.');
        }

        $quotation->load(['items', 'lead.service.stages', 'service.stages']);

        return DB::transaction(function () use ($quotation, $createdByAdminId, $approvingCustomer) {
            // 1. Resolve or create customer
            $customer = $approvingCustomer;
            if (! $customer && $quotation->customer_id) {
                $customer = Customer::find($quotation->customer_id);
            }

            if (! $customer) {
                $customer = Customer::findByPhoneDigits($quotation->customer_phone);
            }

            if (! $customer) {
                $customer = Customer::create([
                    'name' => $quotation->customer_name,
                    'phone' => $quotation->customer_phone,
                    'email' => $quotation->customer_email,
                    'address' => $quotation->customer_address,
                    'area' => $quotation->customer_area,
                    'password' => Str::random(12),
                    'status' => 'active',
                    'lead_id' => $quotation->lead_id,
                ]);
            }

            // 2. Link & convert lead if exists
            $lead = $quotation->lead;
            if ($lead && ! $lead->isConverted()) {
                $lead->update([
                    'status' => 'converted',
                    'converted_customer_id' => $customer->id,
                ]);
            }

            // 3. Resolve service
            $service = $quotation->service ?: ($lead?->service);
            $serviceName = $service?->name ?? 'Agricultural Service Execution';

            // Resolve orchard if lead or customer specifies
            $orchardId = null;
            if ($lead && isset($lead->custom_fields['orchard_id']) && is_numeric($lead->custom_fields['orchard_id'])) {
                $orchardId = (int) $lead->custom_fields['orchard_id'];
            }

            // 4. Create active Work Order
            $notes = trim(implode("\n\n", array_filter([
                "Created from Approved Quotation #{$quotation->number} (Grand Total: ₹" . number_format((float) $quotation->grand_total, 2) . ")",
                $approvingCustomer ? "Approved by customer ({$approvingCustomer->name}) via Mobile App" : null,
                $quotation->notes ? "Quotation notes: {$quotation->notes}" : null,
                $lead?->notes ? "Lead notes: {$lead->notes}" : null,
            ])));

            $workOrder = WorkOrder::create([
                'customer_id' => $customer->id,
                'customer_name' => $customer->name,
                'orchard_id' => $orchardId,
                'service_id' => $service?->id,
                'service_name' => $serviceName,
                'assigned_agent_id' => null,
                'status' => 'in_progress', // Active work order
                'started_at' => now(),
                'notes' => $notes !== '' ? $notes : null,
                'created_by' => $createdByAdminId,
            ]);

            // 5. Populate Work Order Stages & Stage Products
            if ($service && $service->stages->isNotEmpty()) {
                foreach ($service->stages->sortBy('sort_order') as $template) {
                    WorkOrderStage::create([
                        'work_order_id' => $workOrder->id,
                        'service_stage_id' => $template->id,
                        'name' => $template->name,
                        'description' => $template->description,
                        'sort_order' => $template->sort_order,
                        'requires_photo' => $template->requires_photo,
                        'min_photos' => $template->min_photos,
                        'requires_pdf' => $template->requires_pdf,
                    ]);
                }
            }

            // Ensure there is at least one active stage to anchor materials
            $targetStage = $workOrder->stages()->first();
            if (! $targetStage) {
                $targetStage = WorkOrderStage::create([
                    'work_order_id' => $workOrder->id,
                    'service_stage_id' => null,
                    'name' => 'Service Execution',
                    'description' => 'Execution of approved quotation deliverables',
                    'sort_order' => 1,
                ]);
            }

            // Copy quotation items as stage products for operational tracking & invoicing
            foreach ($quotation->items as $item) {
                WorkOrderStageProduct::create([
                    'work_order_stage_id' => $targetStage->id,
                    'product_id' => $item->product_id,
                    'name' => $item->name,
                    'unit' => $item->unit,
                    'quantity' => (float) $item->qty,
                    'rate' => (float) $item->rate,
                    'gst_rate' => (float) $item->gst_rate,
                ]);
            }

            // 6. Update quotation to approved and link work order
            $quotation->update([
                'status' => 'approved',
                'approved_at' => now(),
                'customer_id' => $customer->id,
                'work_order_id' => $workOrder->id,
            ]);

            return $workOrder;
        });
    }
}
