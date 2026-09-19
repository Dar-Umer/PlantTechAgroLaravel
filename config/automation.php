<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Notification Channel
    |--------------------------------------------------------------------------
    |
    | both | database | email
    | Controls which channels admin notifications (low stock, overdue, stale
    | work orders, lead escalations, new leads) are delivered on.
    |
    */

    'channel' => 'both',

    /*
    | Auto-generate the invoice when the last stage of a work order is
    | completed or skipped. Only applies when materials have been recorded.
    */

    'auto_invoice_on_completion' => true,

    /*
    | Overdue invoice tracking. Invoices whose balance is due and whose due
    | date is older than the grace window are marked "Overdue" once.
    */

    'overdue_enabled' => true,
    'overdue_grace_days' => 0,
    'overdue_notify_customer' => true,

    /*
    | Stale work order reminders. Flags assigned/in-progress work orders
    | with no activity for the given number of days.
    */

    'work_order_reminders_enabled' => true,
    'work_order_stale_days' => 7,

    /*
    | Lead escalation. Flags open leads (new/contacted/no_answer/interested)
    | that have not been touched for the given number of days.
    */

    'lead_escalation_enabled' => true,
    'lead_stale_days' => 3,

    /*
    | Send an in-app (and email) alert to active admins when a new lead
    | submits the landing page form.
    */

    'new_lead_alerts_enabled' => true,

    /*
    | On-screen popup + tone when a new lead arrives. The admin layout
    | polls for unread lead notifications every N seconds.
    */

    'new_lead_popup_enabled' => true,
    'new_lead_popup_interval' => 60,
];