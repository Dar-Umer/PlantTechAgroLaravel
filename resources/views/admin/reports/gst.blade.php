@extends('admin.layout')

@section('page-title', 'GST & Tax Reports')

@section('content')
<div class="space-y-6" x-data="{ tab: '{{ request('tab', 'b2b') }}' }">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">GST & Tax Reports (GSTR-1 Ready)</h2>
            <p class="text-sm text-gray-500 mt-1">Tax summaries, B2B/B2C splits, and HSN aggregate data for filing returns.</p>
        </div>
        <div class="flex items-center gap-3">
            <a :href="`{{ route('admin.reports.gst.export') }}?from={{ $from->format('Y-m-d') }}&to={{ $to->format('Y-m-d') }}&tab=${tab}`"
               class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-xl text-gray-700 bg-white hover:bg-gray-50 focus:outline-none">
                <svg class="w-4 h-4 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                Export CSV (<span x-text="tab.toUpperCase()"></span>)
            </a>
        </div>
    </div>

    {{-- Filters --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4">
        <form method="GET" action="{{ route('admin.reports.gst') }}" class="flex flex-wrap items-center gap-4">
            <input type="hidden" name="tab" :value="tab">
            <div class="flex items-center gap-2">
                <label class="text-sm font-medium text-gray-600">From:</label>
                <input type="date" name="from" value="{{ $from->format('Y-m-d') }}" class="px-3 py-1.5 border border-gray-300 rounded-lg text-sm focus:ring-emerald-500 focus:border-emerald-500">
            </div>
            <div class="flex items-center gap-2">
                <label class="text-sm font-medium text-gray-600">To:</label>
                <input type="date" name="to" value="{{ $to->format('Y-m-d') }}" class="px-3 py-1.5 border border-gray-300 rounded-lg text-sm focus:ring-emerald-500 focus:border-emerald-500">
            </div>
            <button type="submit" class="px-4 py-1.5 bg-emerald-600 text-white font-medium text-sm rounded-lg hover:bg-emerald-700 transition">
                Filter
            </button>
            <a href="{{ route('admin.reports.gst') }}" class="text-sm text-gray-500 hover:text-gray-700">Reset</a>
        </form>
    </div>

    {{-- KPI Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
            <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Taxable Value</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">₹{{ number_format($totals['taxable'], 2) }}</p>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
            <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">CGST (Central)</p>
            <p class="text-2xl font-bold text-blue-600 mt-1">₹{{ number_format($totals['cgst'], 2) }}</p>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
            <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">SGST (State/UT)</p>
            <p class="text-2xl font-bold text-indigo-600 mt-1">₹{{ number_format($totals['sgst'], 2) }}</p>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
            <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Total Tax</p>
            <p class="text-2xl font-bold text-amber-600 mt-1">₹{{ number_format($totals['total_tax'], 2) }}</p>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
            <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Gross Turnover</p>
            <p class="text-2xl font-bold text-emerald-600 mt-1">₹{{ number_format($totals['grand'], 2) }}</p>
        </div>
    </div>

    {{-- Tabs --}}
    <div class="border-b border-gray-200">
        <nav class="-mb-px flex space-x-8" aria-label="Tabs">
            <button @click="tab = 'b2b'" :class="tab === 'b2b' ? 'border-emerald-500 text-emerald-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'" class="whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm flex items-center gap-2">
                <span>B2B Supplies (Registered)</span>
                <span class="bg-gray-100 text-gray-600 py-0.5 px-2 rounded-full text-xs">{{ count($b2bInvoices) }}</span>
            </button>
            <button @click="tab = 'b2c'" :class="tab === 'b2c' ? 'border-emerald-500 text-emerald-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'" class="whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm flex items-center gap-2">
                <span>B2C Supplies (Consumer/Unregistered)</span>
                <span class="bg-gray-100 text-gray-600 py-0.5 px-2 rounded-full text-xs">{{ count($b2cInvoices) }}</span>
            </button>
            <button @click="tab = 'hsn'" :class="tab === 'hsn' ? 'border-emerald-500 text-emerald-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'" class="whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm flex items-center gap-2">
                <span>HSN Summary</span>
                <span class="bg-gray-100 text-gray-600 py-0.5 px-2 rounded-full text-xs">{{ count($hsnRows) }}</span>
            </button>
        </nav>
    </div>

    {{-- Content: B2B --}}
    <div x-show="tab === 'b2b'" class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Invoice</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer & GSTIN</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Taxable Value</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">CGST</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">SGST</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total Tax</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Invoice Total</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    @forelse($b2bInvoices as $inv)
                        <tr class="hover:bg-gray-50/80 transition">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="font-medium text-gray-900">{{ $inv['invoice_number'] }}</span>
                                <div class="text-xs text-gray-500">{{ $inv['invoice_date'] }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $inv['customer_name'] }}</div>
                                <div class="text-xs font-mono text-emerald-600 bg-emerald-50 px-1.5 py-0.5 rounded inline-block mt-0.5">{{ $inv['gstin'] }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-900">₹{{ number_format($inv['taxable_value'], 2) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-600">₹{{ number_format($inv['cgst'], 2) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-600">₹{{ number_format($inv['sgst'], 2) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium text-amber-600">₹{{ number_format($inv['total_tax'], 2) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-bold text-gray-900">₹{{ number_format($inv['grand_total'], 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-gray-400">No B2B invoices found in this period.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Content: B2C --}}
    <div x-show="tab === 'b2c'" style="display: none;" class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Invoice</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Taxable Value</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">CGST</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">SGST</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total Tax</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Invoice Total</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    @forelse($b2cInvoices as $inv)
                        <tr class="hover:bg-gray-50/80 transition">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="font-medium text-gray-900">{{ $inv['invoice_number'] }}</span>
                                <div class="text-xs text-gray-500">{{ $inv['invoice_date'] }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $inv['customer_name'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-900">₹{{ number_format($inv['taxable_value'], 2) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-600">₹{{ number_format($inv['cgst'], 2) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-600">₹{{ number_format($inv['sgst'], 2) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium text-amber-600">₹{{ number_format($inv['total_tax'], 2) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-bold text-gray-900">₹{{ number_format($inv['grand_total'], 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-gray-400">No B2C invoices found in this period.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Content: HSN Summary --}}
    <div x-show="tab === 'hsn'" style="display: none;" class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">HSN/SAC</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Qty Sold</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Taxable Value</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">CGST</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">SGST</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total Tax</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total Value</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    @forelse($hsnRows as $hsn)
                        <tr class="hover:bg-gray-50/80 transition">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-mono font-medium text-gray-900">{{ $hsn['hsn_code'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $hsn['description'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-900">{{ $hsn['total_qty'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-900">₹{{ number_format($hsn['taxable_value'], 2) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-600">₹{{ number_format($hsn['cgst'], 2) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-600">₹{{ number_format($hsn['sgst'], 2) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium text-amber-600">₹{{ number_format($hsn['total_tax'], 2) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-bold text-gray-900">₹{{ number_format($hsn['grand_total'], 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center text-gray-400">No HSN aggregate data in this period.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
