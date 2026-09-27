<?php

namespace App\Services;

use App\Models\Setting;

class NotificationTemplateService
{
    public const DEFAULT_TEMPLATES = [
        'work_order_stage_completed' => [
            'name' => 'Work Order Stage Completed',
            'category' => 'Work Orders',
            'recipient' => 'Farmer / Customer',
            'fcm_enabled' => true,
            'title' => 'Stage Completed: {stage_name}',
            'body' => "Stage '{stage_name}' for work order #{work_order_number} has been completed successfully.",
            'available_tags' => ['{stage_name}', '{work_order_number}', '{service_name}', '{orchard_name}', '{customer_name}'],
            'description' => 'Sent to the customer when a field agent or admin completes a stage on an active work order.',
        ],
        'work_order_completed' => [
            'name' => 'All Stages Finished (Work Order Completed)',
            'category' => 'Work Orders',
            'recipient' => 'Farmer / Customer',
            'fcm_enabled' => true,
            'title' => 'Work Order #{work_order_number} Completed 🎉',
            'body' => "All stages for '{service_name}' have been completed successfully.",
            'available_tags' => ['{work_order_number}', '{service_name}', '{orchard_name}', '{customer_name}'],
            'description' => 'Sent when all stages in a service work order have reached completed or skipped status.',
        ],
        'work_order_assigned' => [
            'name' => 'Work Order Assigned to Field Agent',
            'category' => 'Work Orders',
            'recipient' => 'Assigned Agent / Staff',
            'fcm_enabled' => true,
            'title' => 'New Work Order Assigned: #{work_order_number}',
            'body' => "You have been assigned to service '{service_name}' for customer {customer_name}.",
            'available_tags' => ['{work_order_number}', '{service_name}', '{orchard_name}', '{customer_name}'],
            'description' => 'Sent to the assigned field agent when a work order is assigned to them.',
        ],
        'ticket_reply' => [
            'name' => 'Support Ticket Reply',
            'category' => 'Support & Tickets',
            'recipient' => 'Farmer / Customer',
            'fcm_enabled' => true,
            'title' => 'Support reply on #{ticket_number}',
            'body' => '{reply_message}',
            'available_tags' => ['{ticket_number}', '{ticket_subject}', '{reply_message}', '{customer_name}'],
            'description' => 'Sent to the farmer when PTA staff replies to their advisory or support ticket.',
        ],
        'ticket_status_change' => [
            'name' => 'Support Ticket Status Changed',
            'category' => 'Support & Tickets',
            'recipient' => 'Farmer / Customer',
            'fcm_enabled' => true,
            'title' => 'Ticket #{ticket_number} {status_label}',
            'body' => 'Your ticket status is now {status_label}.',
            'available_tags' => ['{ticket_number}', '{ticket_subject}', '{status_label}', '{customer_name}'],
            'description' => 'Sent when staff marks a ticket as in progress, resolved, or closed.',
        ],
        'invoice_overdue' => [
            'name' => 'Invoice Overdue Notice',
            'category' => 'Billing & Invoices',
            'recipient' => 'Farmer / Customer',
            'fcm_enabled' => true,
            'title' => 'Payment Reminder: Invoice #{invoice_number}',
            'body' => 'Invoice #{invoice_number} of ₹{due_amount} is overdue. Please complete your payment.',
            'available_tags' => ['{invoice_number}', '{due_amount}', '{customer_name}'],
            'description' => 'Sent when an unpaid invoice exceeds the overdue grace period.',
        ],
        'new_lead_alert' => [
            'name' => 'New Lead / Enquiry Alert',
            'category' => 'Leads & CRM',
            'recipient' => 'Admins / Agronomists',
            'fcm_enabled' => false,
            'title' => 'New Lead Received: {lead_name}',
            'body' => '{lead_name} submitted a new inquiry for {service_name} ({lead_phone}).',
            'available_tags' => ['{lead_name}', '{lead_phone}', '{service_name}'],
            'description' => 'Sent to admins when a farmer submits a new lead form on the website.',
        ],
        'low_stock_alert' => [
            'name' => 'Low Stock Warning',
            'category' => 'Inventory',
            'recipient' => 'Store Admins',
            'fcm_enabled' => false,
            'title' => 'Low Stock Alert: {product_name}',
            'body' => '{product_name} is running low ({stock_qty} {unit} remaining).',
            'available_tags' => ['{product_name}', '{stock_qty}', '{unit}'],
            'description' => 'Sent when product inventory falls at or below reorder threshold.',
        ],
    ];

    /**
     * Get all templates with saved customizations merged over defaults.
     */
    public static function all(): array
    {
        $saved = Setting::get('notification_templates', []);
        $templates = self::DEFAULT_TEMPLATES;

        foreach ($templates as $key => $template) {
            if (isset($saved[$key]) && is_array($saved[$key])) {
                $templates[$key]['title'] = $saved[$key]['title'] ?? $template['title'];
                $templates[$key]['body'] = $saved[$key]['body'] ?? $template['body'];
                if (isset($saved[$key]['fcm_enabled'])) {
                    $templates[$key]['fcm_enabled'] = (bool) $saved[$key]['fcm_enabled'];
                }
            }
        }

        return $templates;
    }

    /**
     * Get a specific template by key.
     */
    public static function get(string $key): array
    {
        $all = self::all();

        return $all[$key] ?? (self::DEFAULT_TEMPLATES[$key] ?? [
            'title' => 'Notification',
            'body' => '',
            'fcm_enabled' => true,
        ]);
    }

    /**
     * Render a template's title and body with variables.
     */
    public static function render(string $key, array $variables = []): array
    {
        $tpl = self::get($key);
        $title = $tpl['title'];
        $body = $tpl['body'];

        foreach ($variables as $tag => $val) {
            $valStr = (string) ($val ?? '');
            $title = str_replace($tag, $valStr, $title);
            $body = str_replace($tag, $valStr, $body);
        }

        return [
            'title' => $title,
            'body' => $body,
            'fcm_enabled' => $tpl['fcm_enabled'] ?? true,
        ];
    }
}
