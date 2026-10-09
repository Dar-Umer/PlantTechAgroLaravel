@extends('admin.layout')

@section('page-title', 'New Purchase Bill (Inward Stock)')

@section('content')
<div x-data="purchaseBillForm({{ json_encode($products) }}, '{{ $preselectedSupplierId }}')" class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.purchase-bills.index') }}" class="p-2 rounded-xl bg-white border border-gray-200 text-gray-600 hover:text-gray-900 hover:bg-gray-50 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            </a>
            <div>
                <h2 class="text-2xl font-bold text-gray-900 tracking-tight">Record Purchase Bill</h2>
                <p class="text-sm text-gray-500 mt-0.5">Receive supplier inventory, record batches, update stock and track vendor payables.</p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <span class="inline-flex items-center px-3 py-1.5 rounded-xl bg-emerald-50 text-emerald-700 text-xs font-semibold border border-emerald-100">
                Bill # {{ $nextBillNumber }}
            </span>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.purchase-bills.store') }}" enctype="multipart/form-data" class="space-y-6">
        @csrf

        <!-- Bill Metadata Section -->
        <div class="bg-white rounded-2xl shadow-xs border border-gray-100 p-6 space-y-4">
            <h3 class="text-base font-bold text-gray-900 flex items-center gap-2">
                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                Vendor & Invoice Details
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Supplier -->
                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">Supplier / Vendor *</label>
                    <div class="flex gap-2">
                        <select name="supplier_id" x-model="supplierId" required
                                class="flex-1 rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                            <option value="">-- Select Supplier --</option>
                            @foreach($suppliers as $supplier)
                                <option value="{{ $supplier->id }}" {{ (old('supplier_id', $preselectedSupplierId) == $supplier->id) ? 'selected' : '' }}>
                                    {{ $supplier->name }} @if($supplier->phone)({{ $supplier->phone }})@endif
                                </option>
                            @endforeach
                        </select>
                        <a href="{{ route('admin.suppliers.create') }}" target="_blank"
                           class="px-3 py-2 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold flex items-center gap-1 transition" title="Add New Supplier">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            New
                        </a>
                    </div>
                </div>

                <!-- Supplier Invoice / DC Number -->
                <div>
                    <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">Supplier Invoice / DC #</label>
                    <input type="text" name="supplier_invoice_no" value="{{ old('supplier_invoice_no') }}" placeholder="e.g. INV-98432"
                           class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm font-medium focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                </div>

                <!-- Bill Date -->
                <div>
                    <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">Bill Date *</label>
                    <input type="date" name="bill_date" required value="{{ old('bill_date', date('Y-m-d')) }}"
                           class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 pt-2">
                <!-- Due Date -->
                <div>
                    <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">Payment Due Date</label>
                    <input type="date" name="due_date" value="{{ old('due_date') }}"
                           class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                </div>

                <!-- Attachment -->
                <div>
                    <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">Scanned Bill / Receipt (PDF or Image)</label>
                    <input type="file" name="attachment" accept=".pdf,image/*"
                           class="w-full text-xs text-gray-500 file:mr-3 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-gray-100 file:text-gray-700 hover:file:bg-gray-200">
                </div>

                <!-- Notes -->
                <div>
                    <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">Notes / Receiving Remarks</label>
                    <input type="text" name="notes" value="{{ old('notes') }}" placeholder="e.g. Received via Delhivery, good condition"
                           class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                </div>
            </div>
        </div>

        <!-- Line Items Section -->
        <div class="bg-white rounded-2xl shadow-xs border border-gray-100 p-6 space-y-4">
            <div class="flex items-center justify-between pb-2 border-b border-gray-100">
                <div>
                    <h3 class="text-base font-bold text-gray-900 flex items-center gap-2">
                        <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                        Stock Items & Lot Breakdown
                    </h3>
                    <p class="text-xs text-gray-500">Received quantities will automatically increment warehouse inventory and generate lot tracking.</p>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" @click="openProductModal(null)"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold text-brand-700 bg-brand-50 hover:bg-brand-100 border border-brand-200 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        + Quick Add Product
                    </button>
                    <button type="button" @click="addItem()"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        + Add Item Row
                    </button>
                </div>
            </div>

            <!-- Items Table -->
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead>
                        <tr class="text-xs font-semibold text-gray-500 uppercase border-b border-gray-100 bg-gray-50/50">
                            <th class="py-2.5 px-3 min-w-[240px]">Product *</th>
                            <th class="py-2.5 px-3 min-w-[140px]">Batch / Lot #</th>
                            <th class="py-2.5 px-3 min-w-[130px]">Expiry Date</th>
                            <th class="py-2.5 px-3 min-w-[100px] text-right">Qty *</th>
                            <th class="py-2.5 px-3 min-w-[110px] text-right">Unit Cost (₹) *</th>
                            <th class="py-2.5 px-3 min-w-[110px] text-right">Sale Rate (₹)</th>
                            <th class="py-2.5 px-3 min-w-[90px] text-right">Tax %</th>
                            <th class="py-2.5 px-3 min-w-[120px] text-right">Total (₹)</th>
                            <th class="py-2.5 px-2 w-10"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <template x-for="(item, index) in items" :key="index">
                            <tr class="align-top">
                                <!-- Product selector with Quick Add button -->
                                <td class="py-3 px-3">
                                    <div class="flex items-center gap-1.5">
                                        <select :name="`items[${index}][product_id]`"
                                                x-model="item.product_id"
                                                @change="onProductChange(index)"
                                                required
                                                class="flex-1 min-w-0 rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-xs font-medium focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                                            <option value="">-- Choose Product --</option>
                                            <template x-for="p in products" :key="p.id">
                                                <option :value="p.id" x-text="`${p.name} (Stock: ${p.stock_qty} ${p.unit})`"></option>
                                            </template>
                                        </select>
                                        <button type="button" @click="openProductModal(index)"
                                                title="Add New Product immediately"
                                                class="p-2 rounded-xl bg-gray-100 hover:bg-emerald-50 hover:text-emerald-700 text-gray-600 transition flex-shrink-0">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                        </button>
                                    </div>
                                </td>

                                <!-- Batch Number -->
                                <td class="py-3 px-3">
                                    <input type="text" :name="`items[${index}][batch_number]`"
                                           x-model="item.batch_number"
                                           placeholder="e.g. BATCH-2026-A"
                                           class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-xs font-medium focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                                </td>

                                <!-- Expiry Date -->
                                <td class="py-3 px-3">
                                    <input type="date" :name="`items[${index}][expiry_date]`"
                                           x-model="item.expiry_date"
                                           class="w-full rounded-xl border border-gray-200 bg-gray-50 px-2 py-2 text-xs focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                                </td>

                                <!-- Quantity -->
                                <td class="py-3 px-3">
                                    <div class="relative">
                                        <input type="number" step="0.001" min="0.001" :name="`items[${index}][quantity]`"
                                               x-model.number="item.quantity"
                                               required
                                               class="w-full rounded-xl border border-gray-200 bg-gray-50 px-2 py-2 text-xs text-right font-semibold focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                                    </div>
                                    <span class="block text-[10px] text-gray-400 text-right mt-0.5" x-text="item.unit"></span>
                                </td>

                                <!-- Unit Purchase Cost -->
                                <td class="py-3 px-3">
                                    <input type="number" step="0.01" min="0" :name="`items[${index}][unit_cost]`"
                                           x-model.number="item.unit_cost"
                                           required
                                           class="w-full rounded-xl border border-gray-200 bg-gray-50 px-2 py-2 text-xs text-right font-semibold focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                                </td>

                                <!-- Selling Price (Updated rate) -->
                                <td class="py-3 px-3">
                                    <input type="number" step="0.01" min="0" :name="`items[${index}][selling_price]`"
                                           x-model.number="item.selling_price"
                                           placeholder="Rate"
                                           class="w-full rounded-xl border border-gray-200 bg-gray-50 px-2 py-2 text-xs text-right focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                                </td>

                                <!-- Tax % -->
                                <td class="py-3 px-3">
                                    <select :name="`items[${index}][tax_percent]`"
                                            x-model.number="item.tax_percent"
                                            class="w-full rounded-xl border border-gray-200 bg-gray-50 px-2 py-2 text-xs text-right focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                                        <option value="0">0%</option>
                                        <option value="5">5%</option>
                                        <option value="12">12%</option>
                                        <option value="18">18%</option>
                                        <option value="28">28%</option>
                                    </select>
                                </td>

                                <!-- Line Total -->
                                <td class="py-3 px-3 text-right">
                                    <span class="font-bold text-gray-900 text-xs block py-2"
                                          x-text="'₹' + (((item.quantity || 0) * (item.unit_cost || 0)) * (1 + (item.tax_percent || 0)/100)).toFixed(2)">
                                    </span>
                                </td>

                                <!-- Remove row -->
                                <td class="py-3 px-2 text-center">
                                    <button type="button" @click="removeItem(index)"
                                            class="p-1 text-gray-400 hover:text-red-600 transition" title="Remove line">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Calculations & Payments Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Initial Payment Box -->
            <div class="bg-white rounded-2xl shadow-xs border border-gray-100 p-6 space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h4 class="text-sm font-bold text-gray-900">Payment Upon Bill Creation</h4>
                        <p class="text-xs text-gray-500 mt-0.5">Did you pay the vendor upfront or via advance?</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="initial_payment" value="1" x-model="recordInitialPayment"
                               @change="if (recordInitialPayment && (!initialPaymentAmount || initialPaymentAmount <= 0)) initialPaymentAmount = grandTotal"
                               class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-600"></div>
                    </label>
                </div>

                <div x-show="recordInitialPayment" x-cloak class="space-y-4 pt-3 border-t border-gray-100">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">Amount Paid Now (₹) *</label>
                            <div class="flex gap-2">
                                <input type="number" step="0.01" min="0.01" name="initial_payment_amount"
                                       x-model.number="initialPaymentAmount"
                                       :disabled="!recordInitialPayment"
                                       placeholder="0.00"
                                       class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm font-semibold focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100 disabled:opacity-50">
                                <button type="button" @click="initialPaymentAmount = grandTotal"
                                        class="px-2.5 py-1 text-xs font-medium text-emerald-700 bg-emerald-50 hover:bg-emerald-100 rounded-xl transition whitespace-nowrap">
                                    Full
                                </button>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">Payment Method</label>
                            <select name="initial_payment_method"
                                    :disabled="!recordInitialPayment"
                                    class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100 disabled:opacity-50">
                                <option value="bank_transfer">Bank Transfer (NEFT/RTGS)</option>
                                <option value="cheque">Cheque</option>
                                <option value="upi">UPI / QR</option>
                                <option value="cash">Cash</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">Transaction Ref / Cheque / UTR #</label>
                        <input type="text" name="initial_payment_reference" placeholder="e.g. UTR12345678"
                               :disabled="!recordInitialPayment"
                               class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm font-medium focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100 disabled:opacity-50">
                    </div>
                </div>

                <div x-show="!recordInitialPayment" class="p-4 rounded-xl bg-gray-50 text-xs text-gray-500">
                    No payment will be recorded now. This bill will be marked as <span class="font-bold text-red-600">Unpaid</span> and can be settled anytime later from the bill or supplier ledger.
                </div>
            </div>

            <!-- Financial Totals Summary -->
            <div class="bg-white rounded-2xl shadow-xs border border-gray-100 p-6 space-y-3">
                <h4 class="text-sm font-bold text-gray-900 border-b border-gray-100 pb-2">Financial Summary</h4>

                <div class="flex items-center justify-between text-sm text-gray-600">
                    <span>Taxable Subtotal</span>
                    <span class="font-semibold text-gray-900" x-text="'₹' + subtotal.toFixed(2)">₹0.00</span>
                </div>

                <div class="flex items-center justify-between text-sm text-gray-600">
                    <span>Total GST / Taxes</span>
                    <span class="font-semibold text-gray-900" x-text="'₹' + taxTotal.toFixed(2)">₹0.00</span>
                </div>

                <div class="flex items-center justify-between text-sm text-gray-600 gap-4">
                    <span>Discount (₹)</span>
                    <input type="number" step="0.01" min="0" name="discount" x-model.number="discount" placeholder="0.00"
                           class="w-28 rounded-lg border border-gray-200 bg-gray-50 px-2.5 py-1 text-right text-xs focus:outline-none focus:border-brand-500">
                </div>

                <div class="flex items-center justify-between text-sm text-gray-600 gap-4">
                    <span>Freight / Shipping (₹)</span>
                    <input type="number" step="0.01" min="0" name="shipping_cost" x-model.number="shippingCost" placeholder="0.00"
                           class="w-28 rounded-lg border border-gray-200 bg-gray-50 px-2.5 py-1 text-right text-xs focus:outline-none focus:border-brand-500">
                </div>

                <div class="border-t border-gray-100 pt-3 flex items-center justify-between">
                    <span class="text-base font-bold text-gray-900">Grand Total</span>
                    <span class="text-2xl font-bold text-emerald-600" x-text="'₹' + grandTotal.toFixed(2)">₹0.00</span>
                </div>

                <div x-show="recordInitialPayment" class="flex items-center justify-between text-xs text-gray-500 pt-1">
                    <span>Balance Remaining</span>
                    <span class="font-bold text-red-600" x-text="'₹' + Math.max(0, grandTotal - (initialPaymentAmount || 0)).toFixed(2)"></span>
                </div>
            </div>
        </div>

        <!-- Action Submit Buttons -->
        <div class="flex items-center justify-end gap-3 pt-4">
            <a href="{{ route('admin.purchase-bills.index') }}"
               class="px-5 py-2.5 rounded-xl border border-gray-200 text-gray-600 text-sm font-medium hover:bg-gray-50 transition">
                Cancel
            </a>
            <button type="submit"
                    class="px-6 py-2.5 rounded-xl bg-emerald-600 text-white text-sm font-semibold hover:bg-emerald-700 transition shadow-sm flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                Confirm & Receive Inward Stock
            </button>
        </div>
    </form>

    <!-- Quick Add Product Modal -->
    <div x-show="showProductModal"
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto"
         aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="showProductModal"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 @click="showProductModal = false"
                 class="fixed inset-0 bg-gray-900/50 backdrop-blur-xs transition-opacity"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div x-show="showProductModal"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-gray-100">

                <form @submit.prevent="submitNewProduct()" class="p-6 space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                        <div>
                            <h3 class="text-lg font-bold text-gray-900">Add New Product</h3>
                            <p class="text-xs text-gray-500 mt-0.5">Create catalog item instantly without losing your purchase bill draft.</p>
                        </div>
                        <button type="button" @click="showProductModal = false" class="text-gray-400 hover:text-gray-600 p-1">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <!-- General Error Alert -->
                    <template x-if="productModalErrors.general">
                        <div class="p-3 bg-red-50 border border-red-200 rounded-xl text-xs text-red-700" x-text="productModalErrors.general[0]"></div>
                    </template>

                    <!-- Product Name -->
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">Product Name *</label>
                        <input type="text" x-model="newProduct.name" required placeholder="e.g. Red Chief Apple Rootstock"
                               class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm font-medium focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                        <template x-if="productModalErrors.name">
                            <p class="text-[11px] text-red-600 mt-1" x-text="productModalErrors.name[0]"></p>
                        </template>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- SKU -->
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">SKU / Code</label>
                            <input type="text" x-model="newProduct.sku" placeholder="e.g. ROOT-RC-01"
                                   class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm font-medium focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                            <template x-if="productModalErrors.sku">
                                <p class="text-[11px] text-red-600 mt-1" x-text="productModalErrors.sku[0]"></p>
                            </template>
                        </div>

                        <!-- Unit -->
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">Unit *</label>
                            <select x-model="newProduct.unit" required
                                    class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm font-medium focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                                <option value="pcs">Pieces (pcs)</option>
                                <option value="kg">Kilogram (kg)</option>
                                <option value="g">Gram (g)</option>
                                <option value="ltr">Litre (ltr)</option>
                                <option value="ml">Millilitre (ml)</option>
                                <option value="mtr">Metre (mtr)</option>
                                <option value="bag">Bag</option>
                                <option value="box">Box</option>
                                <option value="set">Set</option>
                                <option value="roll">Roll</option>
                                <option value="units">Units</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- Cost / Rate -->
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">Purchase Cost / Rate (₹) *</label>
                            <input type="number" step="0.01" min="0" x-model.number="newProduct.rate" required placeholder="0.00"
                                   class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm font-semibold focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                            <template x-if="productModalErrors.rate">
                                <p class="text-[11px] text-red-600 mt-1" x-text="productModalErrors.rate[0]"></p>
                            </template>
                        </div>

                        <!-- Selling Price -->
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">Selling Price / MRP (₹)</label>
                            <input type="number" step="0.01" min="0" x-model.number="newProduct.selling_price" placeholder="0.00"
                                   class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm font-semibold focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                            <template x-if="productModalErrors.selling_price">
                                <p class="text-[11px] text-red-600 mt-1" x-text="productModalErrors.selling_price[0]"></p>
                            </template>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <!-- GST Rate -->
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">GST Rate (%)</label>
                            <select x-model.number="newProduct.gst_rate"
                                    class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                                <option value="0">0%</option>
                                <option value="5">5%</option>
                                <option value="12">12%</option>
                                <option value="18">18%</option>
                                <option value="28">28%</option>
                            </select>
                        </div>

                        <!-- HSN Code -->
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">HSN Code</label>
                            <input type="text" x-model="newProduct.hsn_code" placeholder="e.g. 0602"
                                   class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                        </div>

                        <!-- Product Type -->
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">Catalog Type</label>
                            <select x-model="newProduct.type"
                                    class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                                <option value="material">Service Material</option>
                                <option value="sellable">Sellable Item</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-3 border-t border-gray-100">
                        <button type="button" @click="showProductModal = false"
                                class="px-4 py-2 rounded-xl border border-gray-200 text-gray-600 text-sm font-medium hover:bg-gray-50 transition">
                            Cancel
                        </button>
                        <button type="submit" :disabled="productModalLoading"
                                class="px-5 py-2 rounded-xl bg-emerald-600 text-white text-sm font-semibold hover:bg-emerald-700 transition shadow-xs flex items-center gap-2 disabled:opacity-50">
                            <svg x-show="productModalLoading" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                            </svg>
                            <span x-text="productModalLoading ? 'Creating...' : 'Save & Select in Bill'"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function purchaseBillForm(productsData, preselectedSupplierId) {
    return {
        supplierId: preselectedSupplierId || '',
        products: productsData,
        discount: 0,
        shippingCost: 0,
        recordInitialPayment: false,
        initialPaymentAmount: null,
        items: [
            {
                product_id: '',
                batch_number: '',
                expiry_date: '',
                quantity: 1,
                unit_cost: 0,
                selling_price: 0,
                tax_percent: 0,
                unit: 'units',
            }
        ],
        // Quick Add Product Modal state
        showProductModal: false,
        productModalTargetRowIndex: null,
        productModalLoading: false,
        productModalErrors: {},
        newProduct: {
            name: '',
            sku: '',
            unit: 'pcs',
            type: 'material',
            rate: '',
            selling_price: '',
            gst_rate: 0,
            hsn_code: '',
            low_stock_threshold: '',
        },
        openProductModal(targetIndex = null) {
            this.productModalTargetRowIndex = targetIndex;
            this.newProduct = {
                name: '',
                sku: '',
                unit: 'pcs',
                type: 'material',
                rate: '',
                selling_price: '',
                gst_rate: 0,
                hsn_code: '',
                low_stock_threshold: '',
            };
            this.productModalErrors = {};
            this.showProductModal = true;
        },
        async submitNewProduct() {
            this.productModalErrors = {};
            if (!this.newProduct.name || !this.newProduct.name.trim()) {
                this.productModalErrors = { name: ['Product name is required.'] };
                return;
            }
            if (this.newProduct.rate === '' || isNaN(this.newProduct.rate) || Number(this.newProduct.rate) < 0) {
                this.productModalErrors = { rate: ['Please provide a valid purchase rate / cost (>= 0).'] };
                return;
            }

            this.productModalLoading = true;
            try {
                const payload = {
                    name: this.newProduct.name.trim(),
                    sku: this.newProduct.sku && this.newProduct.sku.trim() ? this.newProduct.sku.trim() : null,
                    unit: this.newProduct.unit,
                    type: this.newProduct.type || 'material',
                    rate: parseFloat(this.newProduct.rate) || 0,
                    selling_price: this.newProduct.selling_price && !isNaN(this.newProduct.selling_price) ? parseFloat(this.newProduct.selling_price) : null,
                    gst_rate: this.newProduct.gst_rate !== '' && !isNaN(this.newProduct.gst_rate) ? parseFloat(this.newProduct.gst_rate) : 0,
                    hsn_code: this.newProduct.hsn_code && this.newProduct.hsn_code.trim() ? this.newProduct.hsn_code.trim() : null,
                    low_stock_threshold: this.newProduct.low_stock_threshold && !isNaN(this.newProduct.low_stock_threshold) ? parseFloat(this.newProduct.low_stock_threshold) : null,
                    supplier_id: this.supplierId ? parseInt(this.supplierId) : null,
                    is_active: 1,
                };

                const res = await fetch('{{ route('admin.products.store') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    },
                    body: JSON.stringify(payload),
                });

                const data = await res.json();

                if (!res.ok) {
                    if (res.status === 422 && data.errors) {
                        this.productModalErrors = data.errors;
                    } else {
                        this.productModalErrors = { general: [data.message || 'Failed to create product.'] };
                    }
                    return;
                }

                const newProd = data.product;
                // Prepend to products list so it shows in all line item dropdowns
                this.products.unshift(newProd);

                // Target row selection
                let targetIdx = this.productModalTargetRowIndex;
                if (targetIdx === null) {
                    const emptyIdx = this.items.findIndex(it => !it.product_id);
                    if (emptyIdx !== -1) {
                        targetIdx = emptyIdx;
                    } else {
                        this.addItem();
                        targetIdx = this.items.length - 1;
                    }
                }

                if (this.items[targetIdx]) {
                    this.items[targetIdx].product_id = newProd.id;
                    this.onProductChange(targetIdx);
                    if (newProd.rate > 0) {
                        this.items[targetIdx].unit_cost = newProd.rate;
                    }
                    if (newProd.selling_price > 0) {
                        this.items[targetIdx].selling_price = newProd.selling_price;
                    }
                    if (newProd.gst_rate > 0) {
                        this.items[targetIdx].tax_percent = newProd.gst_rate;
                    }
                }

                this.showProductModal = false;
            } catch (err) {
                this.productModalErrors = { general: [err.message || 'An unexpected error occurred.'] };
            } finally {
                this.productModalLoading = false;
            }
        },
        addItem() {
            this.items.push({
                product_id: '',
                batch_number: '',
                expiry_date: '',
                quantity: 1,
                unit_cost: 0,
                selling_price: 0,
                tax_percent: 0,
                unit: 'units',
            });
        },
        removeItem(index) {
            if (this.items.length > 1) {
                this.items.splice(index, 1);
            }
        },
        onProductChange(index) {
            const item = this.items[index];
            const p = this.products.find(prod => prod.id == item.product_id);
            if (p) {
                item.unit = p.unit || 'units';
                item.unit_cost = parseFloat(p.cost_price || p.rate) || 0;
                item.selling_price = parseFloat(p.selling_price || p.rate) || 0;
                if (p.gst_rate !== undefined && p.gst_rate !== null) {
                    item.tax_percent = parseFloat(p.gst_rate) || 0;
                }
            }
        },
        get subtotal() {
            return this.items.reduce((sum, item) => sum + ((parseFloat(item.quantity) || 0) * (parseFloat(item.unit_cost) || 0)), 0);
        },
        get taxTotal() {
            return this.items.reduce((sum, item) => {
                const base = (parseFloat(item.quantity) || 0) * (parseFloat(item.unit_cost) || 0);
                const taxRate = parseFloat(item.tax_percent) || 0;
                return sum + (base * taxRate / 100);
            }, 0);
        },
        get grandTotal() {
            const sub = this.subtotal;
            const tax = this.taxTotal;
            const disc = parseFloat(this.discount) || 0;
            const ship = parseFloat(this.shippingCost) || 0;
            return Math.max(0, sub + tax - disc + ship);
        }
    };
}
</script>
@endsection
