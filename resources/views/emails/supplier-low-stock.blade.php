@component('mail::message')
# Low Stock Alert

Hello {{ $supplier->contact_person ?: $supplier->name }},

The following material supplied by you has reached its low stock threshold:

**{{ $product->name }}**@if($product->sku) (SKU: {{ $product->sku }})@endif

- **Current stock:** {{ $product->stock_qty }} {{ $product->unit }}
- **Low stock threshold:** {{ $product->low_stock_threshold }} {{ $product->unit }}

Please arrange replenishment at the earliest.

@component('mail::button', ['url' => url('/admin/products')])
View in Admin Panel
@endcomponent

Thanks,<br>
{{ config('invoice.company_name', config('shop.site_name', 'Plant Tech Agro')) }}
@endcomponent
