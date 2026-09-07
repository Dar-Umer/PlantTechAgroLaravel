@component('mail::message')
# Payment Overdue

Hello {{ $invoice->customer_name }},

Our records show that **Invoice {{ $invoice->number }}** is now past its due date.

- **Invoice date:** {{ $invoice->invoice_date?->format('d M Y') }}
- **Due date:** {{ $invoice->due_date?->format('d M Y') ?: '—' }}
- **Balance due:** ₹{{ number_format($invoice->balanceDue(), 0) }}

Please settle this amount at your earliest convenience. If you have already made the payment, kindly ignore this reminder.

@component('mail::button', ['url' => url('/')])
Visit Our Website
@endcomponent

Thank you,<br>
{{ config('invoice.company_name', config('shop.site_name', 'Plant Tech Agro')) }}
@if(config('invoice.phone'))
<br>{{ config('invoice.phone') }}
@endif
@if(config('invoice.email'))
<br>{{ config('invoice.email') }}
@endif
@endcomponent