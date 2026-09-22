@extends('admin.layout')

@section('page-title', 'Point of Sale (POS)')

@section('content')
<div x-data="posTerminal()" x-init="init()" class="-m-4 lg:-m-6 flex flex-col h-[calc(100dvh-4.25rem)] lg:h-[calc(100vh-4.25rem)] bg-gray-100 overflow-hidden select-none">
    {{-- POS Header Bar --}}
    <header class="bg-white border-b border-gray-200 px-4 lg:px-6 py-2.5 sm:py-3 flex items-center justify-between flex-shrink-0 shadow-xs z-20">
        <div class="flex items-center gap-3">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 sm:w-9 sm:h-9 rounded-xl bg-brand-600 text-white flex items-center justify-center font-bold shadow-xs">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                </div>
                <div>
                    <h1 class="text-base sm:text-lg font-bold text-gray-900 leading-tight">POS Terminal</h1>
                    <p class="text-[11px] text-gray-500 hidden sm:block">Plant Tech Agro Billing</p>
                </div>
            </div>
            <span class="hidden md:inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5 animate-pulse"></span>
                FIFO Engine Active
            </span>
        </div>

        <div class="flex items-center gap-2 sm:gap-3">
            {{-- Mobile Segmented Tab Switcher --}}
            <div class="lg:hidden flex items-center p-0.5 bg-gray-100 rounded-xl border border-gray-200">
                <button type="button"
                        @click="mobileTab = 'catalog'"
                        :class="mobileTab === 'catalog' ? 'bg-white text-gray-900 font-bold shadow-xs' : 'text-gray-500 font-medium'"
                        class="px-2.5 py-1 text-xs rounded-lg transition flex items-center gap-1">
                    <span>Items</span>
                </button>
                <button type="button"
                        @click="mobileTab = 'cart'"
                        :class="mobileTab === 'cart' ? 'bg-brand-600 text-white font-bold shadow-xs' : 'text-gray-500 font-medium'"
                        class="px-2.5 py-1 text-xs rounded-lg transition flex items-center gap-1">
                    <span>Cart</span>
                    <span x-show="cartTotalQty > 0"
                          :class="mobileTab === 'cart' ? 'bg-white text-brand-700' : 'bg-brand-600 text-white'"
                          class="px-1.5 py-0.2 rounded-full text-[10px] font-extrabold"
                          x-text="cartTotalQty"></span>
                </button>
            </div>

            <a href="{{ route('admin.pos.sales') }}" class="inline-flex items-center px-2.5 sm:px-3 py-1.5 sm:py-2 text-xs font-semibold text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-xl transition">
                <svg class="w-4 h-4 sm:mr-1.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                <span class="hidden sm:inline">Sales Invoices</span>
            </a>
            <div class="text-right hidden sm:block">
                <p class="text-xs font-semibold text-gray-900">{{ auth('admin')->user()->name }}</p>
                <p class="text-[11px] text-gray-400" x-text="clockTime"></p>
            </div>
        </div>
    </header>

    {{-- Main POS Work Area: Responsive Catalog & Cart --}}
    <div class="flex-1 flex flex-col lg:flex-row overflow-hidden relative">
        {{-- Product Catalogue (Full on mobile when tab = 'catalog', 60% on desktop) --}}
        <div class="flex-1 flex flex-col border-r border-gray-200 bg-gray-50 overflow-hidden"
             :class="mobileTab === 'catalog' ? 'flex' : 'hidden lg:flex'">
            {{-- Search & Category Filter --}}
            <div class="p-3 sm:p-4 bg-white border-b border-gray-200 space-y-2.5 sm:space-y-3 flex-shrink-0">
                <div class="relative">
                    <input type="text"
                           x-ref="searchInput"
                           x-model="searchQuery"
                           @input="filterProducts()"
                           placeholder="Scan barcode or search by name, SKU..."
                           class="w-full pl-9 pr-9 py-2 sm:py-2.5 bg-gray-100 border-none rounded-xl text-xs sm:text-sm text-gray-900 focus:bg-white focus:ring-2 focus:ring-brand-500 transition shadow-inner">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5 text-gray-400 absolute left-3 top-2.5 sm:top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <button type="button" x-show="searchQuery" @click="searchQuery = ''; filterProducts()" class="absolute right-3 top-2.5 sm:top-3 text-gray-400 hover:text-gray-600">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="flex items-center gap-1.5 sm:gap-2 overflow-x-auto pb-1 text-xs scrollbar-none">
                    <button type="button"
                            @click="filterType = 'all'; filterProducts()"
                            :class="filterType === 'all' ? 'bg-brand-600 text-white shadow-xs' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                            class="px-2.5 sm:px-3 py-1.5 rounded-lg font-medium transition flex-shrink-0">
                        All Items (<span x-text="allProducts.length"></span>)
                    </button>
                    <button type="button"
                            @click="filterType = 'sellable'; filterProducts()"
                            :class="filterType === 'sellable' ? 'bg-brand-600 text-white shadow-xs' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                            class="px-2.5 sm:px-3 py-1.5 rounded-lg font-medium transition flex-shrink-0">
                        Retail Products
                    </button>
                    <button type="button"
                            @click="filterType = 'material'; filterProducts()"
                            :class="filterType === 'material' ? 'bg-brand-600 text-white shadow-xs' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                            class="px-2.5 sm:px-3 py-1.5 rounded-lg font-medium transition flex-shrink-0">
                        Materials
                    </button>
                </div>
            </div>

            {{-- Products Grid --}}
            <div class="flex-1 overflow-y-auto p-3 sm:p-4 pb-24 lg:pb-4">
                <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-4 gap-2.5 sm:gap-3">
                    <template x-for="product in filteredProducts" :key="product.id">
                        <div @click="addToCart(product)"
                             class="bg-white rounded-xl border border-gray-200 p-2.5 sm:p-3.5 flex flex-col justify-between hover:border-brand-500 hover:shadow-md transition cursor-pointer group active:scale-[0.98]">
                            <div>
                                <div class="flex items-start justify-between gap-1 mb-1">
                                    <span class="text-[9px] sm:text-[10px] font-mono uppercase px-1.5 py-0.5 rounded bg-gray-100 text-gray-600 truncate max-w-[80px]" x-text="product.sku || 'SKU'"></span>
                                    <span class="text-[10px] sm:text-[11px] font-bold px-1.5 sm:px-2 py-0.5 rounded-full flex-shrink-0"
                                          :class="product.stock <= 0 ? 'bg-red-50 text-red-600' : 'bg-green-50 text-green-700'"
                                          x-text="product.stock + ' ' + product.unit"></span>
                                </div>
                                <h3 class="text-xs sm:text-sm font-semibold text-gray-900 group-hover:text-brand-600 transition line-clamp-2 leading-snug" x-text="product.name"></h3>
                            </div>

                            <div class="mt-2.5 pt-2 border-t border-gray-100 flex items-center justify-between">
                                <div>
                                    <span class="text-sm sm:text-base font-bold text-gray-900" x-text="'₹' + Number(product.rate).toFixed(0)"></span>
                                    <span class="text-[9px] sm:text-[10px] text-gray-400 block" x-text="product.gst_rate > 0 ? '+ ' + product.gst_rate + '% GST' : 'Tax Incl.'"></span>
                                </div>
                                <button type="button" class="w-7 h-7 sm:w-8 sm:h-8 rounded-lg bg-brand-50 text-brand-700 group-hover:bg-brand-600 group-hover:text-white flex items-center justify-center transition shadow-2xs">
                                    <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                                </button>
                            </div>
                        </div>
                    </template>
                </div>

                <div x-show="filteredProducts.length === 0" class="text-center py-16 text-gray-400">
                    <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                    <p class="text-sm font-medium">No matching products found</p>
                    <p class="text-xs mt-1">Try another search term or filter</p>
                </div>
            </div>

            {{-- Floating Mobile Quick Checkout Bar (Only on mobile when cart has items) --}}
            <div x-show="cart.length > 0"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="translate-y-full opacity-0"
                 x-transition:enter-end="translate-y-0 opacity-100"
                 class="lg:hidden fixed bottom-0 inset-x-0 bg-white border-t border-gray-200 px-4 py-3 z-30 shadow-2xl flex items-center justify-between gap-3">
                <div>
                    <span class="text-xs text-gray-500 block"><span class="font-bold text-gray-900" x-text="cartTotalQty"></span> items in cart</span>
                    <span class="text-base font-extrabold text-brand-700 leading-none" x-text="'₹' + cartGrandTotal.toFixed(0)"></span>
                </div>
                <button type="button"
                        @click="mobileTab = 'cart'"
                        class="px-5 py-2.5 bg-brand-600 hover:bg-brand-700 text-white rounded-xl font-bold text-xs shadow-md transition flex items-center gap-1.5 active:scale-95">
                    <span>View Cart & Pay</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </button>
            </div>
        </div>

        {{-- Cart & Checkout (Full on mobile when tab = 'cart', 40% on desktop) --}}
        <div class="w-full lg:w-[460px] xl:w-[500px] flex flex-col bg-white border-t lg:border-t-0 shadow-lg z-10 flex-shrink-0"
             :class="mobileTab === 'cart' ? 'flex' : 'hidden lg:flex'">

            {{-- Mobile Cart Header Navigation --}}
            <div class="lg:hidden px-4 py-2.5 bg-gray-50 border-b border-gray-200 flex items-center justify-between">
                <button type="button" @click="mobileTab = 'catalog'" class="text-xs font-bold text-brand-600 flex items-center gap-1 hover:text-brand-800">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    Add More Items
                </button>
                <button type="button" x-show="cart.length > 0" @click="clearCartConfirm()" class="text-[11px] font-semibold text-red-500 hover:text-red-700">
                    Clear Cart
                </button>
            </div>

            {{-- Customer Selector Header --}}
            <div class="p-3 sm:p-3.5 border-b border-gray-200 bg-gray-50/80 flex items-center justify-between gap-2 flex-shrink-0">
                <div class="flex-1 min-w-0">
                    <label class="block text-[10px] sm:text-[11px] font-semibold uppercase text-gray-500 tracking-wider mb-1">Customer</label>
                    <div class="flex items-center gap-2">
                        <select x-model="selectedCustomerId" @change="onCustomerChange()" class="w-full text-xs font-medium bg-white border border-gray-200 rounded-lg px-2.5 py-1.5 focus:outline-none focus:border-brand-500">
                            <option value="">Walk-in Customer (Retail)</option>
                            <template x-for="cust in customers" :key="cust.id">
                                <option :value="cust.id" x-text="cust.name + (cust.phone ? ' (' + cust.phone + ')' : '')"></option>
                            </template>
                        </select>
                    </div>
                </div>
                <button type="button" @click="showAddCustomer = true" class="mt-4 px-2.5 py-1.5 text-xs font-semibold text-brand-700 bg-brand-50 hover:bg-brand-100 rounded-lg border border-brand-200 transition flex items-center gap-1 flex-shrink-0">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    New
                </button>
            </div>

            {{-- Cart Items List --}}
            <div class="flex-1 overflow-y-auto p-3 sm:p-3.5 divide-y divide-gray-100 space-y-2">
                <template x-for="(item, idx) in cart" :key="item.product_id">
                    <div class="pt-2 first:pt-0">
                        <div class="flex items-start justify-between gap-2">
                            <div class="flex-1 min-w-0">
                                <h4 class="text-xs font-semibold text-gray-900 truncate" x-text="item.name"></h4>
                                <div class="flex items-center gap-2 mt-0.5 text-[11px] text-gray-500">
                                    <span>₹<span x-text="item.unit_price"></span> / <span x-text="item.unit"></span></span>
                                    <span x-show="item.tax_rate > 0" class="text-gray-400">(+<span x-text="item.tax_rate"></span>% GST)</span>
                                </div>
                            </div>
                            <div class="text-right flex items-center gap-1.5">
                                <span class="text-xs font-bold text-gray-900" x-text="'₹' + itemTotal(item).toFixed(2)"></span>
                                <button type="button" @click="removeFromCart(idx)" class="text-gray-400 hover:text-red-500 p-1 rounded hover:bg-gray-100">
                                    <svg class="w-4 h-4 text-gray-400 hover:text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </div>
                        </div>

                        {{-- Quantity Stepper (Enlarged for touch screens) --}}
                        <div class="flex items-center justify-between mt-2">
                            <div class="flex items-center border border-gray-200 rounded-lg bg-gray-50">
                                <button type="button" @click="updateQty(item, -1)" class="w-8 h-8 sm:w-7 sm:h-7 flex items-center justify-center text-gray-600 hover:bg-gray-200 active:bg-gray-300 rounded-l-lg transition font-bold text-base sm:text-sm">-</button>
                                <input type="number"
                                       x-model.number="item.quantity"
                                       @input="if(item.quantity < 0.01) item.quantity = 1"
                                       step="1" min="0.01"
                                       class="w-12 text-center text-xs font-bold bg-transparent border-0 focus:ring-0 p-0 text-gray-900">
                                <button type="button" @click="updateQty(item, 1)" class="w-8 h-8 sm:w-7 sm:h-7 flex items-center justify-center text-gray-600 hover:bg-gray-200 active:bg-gray-300 rounded-r-lg transition font-bold text-base sm:text-sm">+</button>
                            </div>
                            <span class="text-[9px] sm:text-[10px] text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-100 font-mono">FIFO Auto-Lot</span>
                        </div>
                    </div>
                </template>

                <div x-show="cart.length === 0" class="h-full flex flex-col items-center justify-center text-gray-400 py-12">
                    <svg class="w-12 h-12 text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    <p class="text-xs font-medium">Cart is empty</p>
                    <button type="button" @click="mobileTab = 'catalog'" class="lg:hidden mt-2 text-xs font-bold text-brand-600 underline">Browse Products</button>
                </div>
            </div>

            {{-- Cart Calculations & Payment Area --}}
            <div class="p-3.5 sm:p-4 bg-gray-50 border-t border-gray-200 space-y-2.5 sm:space-y-3 flex-shrink-0">
                <div class="space-y-1 text-xs text-gray-600">
                    <div class="flex justify-between">
                        <span>Subtotal</span>
                        <span class="font-medium text-gray-900" x-text="'₹' + cartSubtotal.toFixed(2)"></span>
                    </div>
                    <div class="flex justify-between">
                        <span>GST / Taxes</span>
                        <span class="font-medium text-gray-900" x-text="'₹' + cartTax.toFixed(2)"></span>
                    </div>
                    <div class="flex items-center justify-between gap-2">
                        <span>Discount (₹)</span>
                        <input type="number" x-model.number="orderDiscount" min="0" step="1" placeholder="0" class="w-20 text-right text-xs bg-white border border-gray-200 rounded px-2 py-0.5 font-medium">
                    </div>
                    <div class="flex justify-between" x-show="cartRoundOff !== 0">
                        <span>Round Off</span>
                        <span class="text-gray-500" x-text="(cartRoundOff > 0 ? '+' : '') + cartRoundOff.toFixed(2)"></span>
                    </div>
                    <div class="flex justify-between pt-1.5 border-t border-gray-200 text-sm sm:text-base font-extrabold text-gray-900">
                        <span>Grand Total</span>
                        <span class="text-brand-700 text-base sm:text-lg" x-text="'₹' + cartGrandTotal.toFixed(0)"></span>
                    </div>
                </div>

                {{-- Payment Method Pills (2x2 on mobile, 4x1 on desktop) --}}
                <div>
                    <label class="block text-[10px] sm:text-[11px] font-semibold text-gray-500 uppercase tracking-wider mb-1">Payment Method</label>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-1.5">
                        <button type="button" @click="paymentMethod = 'cash'"
                                :class="paymentMethod === 'cash' ? 'bg-emerald-600 text-white font-bold shadow-xs' : 'bg-white text-gray-700 border border-gray-200'"
                                class="py-2 rounded-lg text-xs transition active:scale-95">Cash</button>
                        <button type="button" @click="paymentMethod = 'upi'"
                                :class="paymentMethod === 'upi' ? 'bg-indigo-600 text-white font-bold shadow-xs' : 'bg-white text-gray-700 border border-gray-200'"
                                class="py-2 rounded-lg text-xs transition active:scale-95">UPI / QR</button>
                        <button type="button" @click="paymentMethod = 'card'"
                                :class="paymentMethod === 'card' ? 'bg-blue-600 text-white font-bold shadow-xs' : 'bg-white text-gray-700 border border-gray-200'"
                                class="py-2 rounded-lg text-xs transition active:scale-95">Card</button>
                        <button type="button" @click="paymentMethod = 'bank_transfer'"
                                :class="paymentMethod === 'bank_transfer' ? 'bg-gray-800 text-white font-bold shadow-xs' : 'bg-white text-gray-700 border border-gray-200'"
                                class="py-2 rounded-lg text-xs transition active:scale-95">NetBank</button>
                    </div>
                </div>

                {{-- Cash Tender / Quick Chips & Change --}}
                <div x-show="paymentMethod === 'cash'" class="bg-white p-2.5 rounded-xl border border-gray-200 space-y-2">
                    <div class="flex items-center justify-between gap-2">
                        <span class="text-xs font-semibold text-gray-700">Cash Received:</span>
                        <input type="number" x-model.number="amountTendered" :placeholder="cartGrandTotal" class="w-28 text-right font-bold text-sm bg-gray-50 border border-gray-200 rounded-lg px-2.5 py-1">
                    </div>

                    {{-- Quick Cash Chips for Fast Touch Entry --}}
                    <div class="flex items-center gap-1 overflow-x-auto text-[10px] pt-1">
                        <button type="button" @click="amountTendered = cartGrandTotal" class="px-2 py-0.5 rounded bg-gray-100 hover:bg-gray-200 font-semibold text-gray-700 flex-shrink-0">
                            Exact (₹<span x-text="cartGrandTotal"></span>)
                        </button>
                        <button type="button" @click="amountTendered = roundUp(cartGrandTotal, 100)" class="px-2 py-0.5 rounded bg-gray-100 hover:bg-gray-200 font-semibold text-gray-700 flex-shrink-0">
                            ₹<span x-text="roundUp(cartGrandTotal, 100)"></span>
                        </button>
                        <button type="button" @click="amountTendered = roundUp(cartGrandTotal, 500)" class="px-2 py-0.5 rounded bg-gray-100 hover:bg-gray-200 font-semibold text-gray-700 flex-shrink-0">
                            ₹<span x-text="roundUp(cartGrandTotal, 500)"></span>
                        </button>
                        <button type="button" @click="amountTendered = 2000" class="px-2 py-0.5 rounded bg-gray-100 hover:bg-gray-200 font-semibold text-gray-700 flex-shrink-0">
                            ₹2000
                        </button>
                    </div>

                    <div class="flex items-center justify-between text-xs pt-1 border-t border-gray-100">
                        <span class="text-gray-500">Change Return:</span>
                        <span class="font-extrabold text-sm" :class="changeDue < 0 ? 'text-red-500' : 'text-emerald-600'" x-text="'₹' + Math.max(0, changeDue).toFixed(0)"></span>
                    </div>
                </div>

                {{-- Complete Sale Button (Large thumb friendly) --}}
                <button type="button"
                        @click="processCheckout()"
                        :disabled="cart.length === 0 || isProcessing"
                        class="w-full py-3.5 px-4 rounded-xl font-bold text-sm text-white bg-brand-600 hover:bg-brand-700 active:scale-[0.99] disabled:opacity-50 disabled:cursor-not-allowed shadow-md transition flex items-center justify-center gap-2">
                    <span x-show="!isProcessing" class="flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        Complete Sale (<span x-text="'₹' + cartGrandTotal.toFixed(0)"></span>)
                    </span>
                    <span x-show="isProcessing" class="flex items-center gap-2">
                        <svg class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                        Processing Sale...
                    </span>
                </button>
            </div>
        </div>
    </div>

    {{-- Sale Completed Modal (Mobile responsive) --}}
    <div x-show="completedSale" x-cloak class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-3 sm:p-4 bg-black/60 backdrop-blur-sm">
        <div class="bg-white rounded-2xl max-w-sm sm:max-w-md w-full p-5 sm:p-6 text-center space-y-4 shadow-2xl animate-in fade-in zoom-in duration-200">
            <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center mx-auto">
                <svg class="w-7 h-7 sm:w-8 sm:h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
            </div>
            <div>
                <h3 class="text-lg sm:text-xl font-bold text-gray-900">Sale Completed!</h3>
                <p class="text-xs sm:text-sm text-gray-500 mt-1">Invoice: <strong class="text-gray-900 font-mono" x-text="completedSale?.invoice_number"></strong></p>
                <p class="text-2xl font-black text-brand-600 mt-2" x-text="'₹' + completedSale?.grand_total"></p>
            </div>

            <div class="grid grid-cols-2 gap-2.5 sm:gap-3 pt-2">
                <a :href="completedSale?.receipt_url" target="_blank" class="py-2.5 px-3 bg-gray-100 hover:bg-gray-200 active:bg-gray-300 text-gray-800 rounded-xl font-semibold text-xs transition flex items-center justify-center gap-1.5">
                    <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    80mm Receipt
                </a>
                <a :href="completedSale?.invoice_url" target="_blank" class="py-2.5 px-3 bg-brand-50 hover:bg-brand-100 active:bg-brand-200 text-brand-700 rounded-xl font-semibold text-xs transition flex items-center justify-center gap-1.5">
                    <svg class="w-4 h-4 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    A4 Invoice
                </a>
            </div>

            <button type="button" @click="resetCart()" class="w-full py-3 bg-brand-600 hover:bg-brand-700 active:scale-[0.99] text-white rounded-xl font-bold text-sm transition">
                Next Customer / New Sale
            </button>
        </div>
    </div>

    {{-- Add POS Customer Modal --}}
    <div x-show="showAddCustomer" x-cloak class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-3 sm:p-4 bg-black/60 backdrop-blur-sm">
        <div class="bg-white rounded-2xl max-w-sm sm:max-w-md w-full p-5 sm:p-6 space-y-4 shadow-2xl" @click.away="showAddCustomer = false">
            <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                <h3 class="text-base font-bold text-gray-900">Add POS Customer</h3>
                <button type="button" @click="showAddCustomer = false" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form @submit.prevent="saveCustomer()" class="space-y-3 text-xs">
                <div>
                    <label class="block font-semibold text-gray-700 mb-1">Customer Name <span class="text-red-500">*</span></label>
                    <input type="text" x-model="newCustomer.name" required class="w-full rounded-xl border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:ring-1 focus:ring-brand-500">
                </div>
                <div>
                    <label class="block font-semibold text-gray-700 mb-1">Phone Number</label>
                    <input type="text" x-model="newCustomer.phone" class="w-full rounded-xl border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:ring-1 focus:ring-brand-500">
                </div>
                <div>
                    <label class="block font-semibold text-gray-700 mb-1">GSTIN (Optional, for B2B bill)</label>
                    <input type="text" x-model="newCustomer.gstin" class="w-full rounded-xl border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:ring-1 focus:ring-brand-500 font-mono">
                </div>
                <div>
                    <label class="block font-semibold text-gray-700 mb-1">Address</label>
                    <textarea x-model="newCustomer.address" rows="2" class="w-full rounded-xl border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:ring-1 focus:ring-brand-500"></textarea>
                </div>

                <div class="flex justify-end gap-2 pt-3">
                    <button type="button" @click="showAddCustomer = false" class="px-4 py-2 rounded-xl text-gray-600 hover:bg-gray-100 font-semibold">Cancel</button>
                    <button type="submit" class="px-4 py-2 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-bold">Save & Select</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function posTerminal() {
        return {
            allProducts: @js($products->map(fn($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'sku' => $p->sku,
                'type' => $p->type,
                'unit' => $p->unit,
                'stock' => (float) $p->stock_qty,
                'rate' => (float) ($p->selling_price ?? $p->rate),
                'cost' => (float) ($p->rate ?? 0),
                'gst_rate' => (float) ($p->gst_rate ?? 0),
            ])->values()),
            filteredProducts: [],
            searchQuery: '',
            filterType: 'all',
            customers: @js($customers),
            selectedCustomerId: '',
            cart: [],
            orderDiscount: 0,
            paymentMethod: 'cash',
            amountTendered: null,
            isProcessing: false,
            completedSale: null,
            showAddCustomer: false,
            newCustomer: { name: '', phone: '', email: '', gstin: '', address: '' },
            clockTime: '',
            mobileTab: 'catalog', // 'catalog' | 'cart'

            init() {
                this.filteredProducts = this.allProducts;
                this.updateClock();
                setInterval(() => this.updateClock(), 1000);
            },

            updateClock() {
                const now = new Date();
                this.clockTime = now.toLocaleDateString('en-IN', { day: '2-digit', month: 'short' }) + ' ' + now.toLocaleTimeString('en-IN', { hour: '2-digit', minute: '2-digit' });
            },

            filterProducts() {
                const q = this.searchQuery.toLowerCase().trim();
                this.filteredProducts = this.allProducts.filter(p => {
                    const matchesType = this.filterType === 'all' || p.type === this.filterType;
                    const matchesQuery = !q || p.name.toLowerCase().includes(q) || (p.sku && p.sku.toLowerCase().includes(q));
                    return matchesType && matchesQuery;
                });
            },

            addToCart(product) {
                const existing = this.cart.find(item => item.product_id === product.id);
                if (existing) {
                    existing.quantity += 1;
                } else {
                    this.cart.push({
                        product_id: product.id,
                        name: product.name,
                        unit: product.unit,
                        unit_price: product.rate,
                        cost_price: product.cost,
                        tax_rate: product.gst_rate,
                        quantity: 1,
                        batch_id: null,
                    });
                }
            },

            removeFromCart(idx) {
                this.cart.splice(idx, 1);
            },

            clearCartConfirm() {
                if (confirm('Clear all items from the current cart?')) {
                    this.cart = [];
                    this.mobileTab = 'catalog';
                }
            },

            updateQty(item, delta) {
                item.quantity = Math.max(0.01, item.quantity + delta);
            },

            itemTotal(item) {
                const sub = item.unit_price * item.quantity;
                const tax = sub * (item.tax_rate / 100);
                return sub + tax;
            },

            get cartTotalQty() {
                return this.cart.reduce((sum, item) => sum + item.quantity, 0);
            },

            get cartSubtotal() {
                return this.cart.reduce((sum, item) => sum + (item.unit_price * item.quantity), 0);
            },

            get cartTax() {
                return this.cart.reduce((sum, item) => sum + ((item.unit_price * item.quantity) * (item.tax_rate / 100)), 0);
            },

            get cartGrandTotal() {
                const raw = Math.max(0, this.cartSubtotal + this.cartTax - (this.orderDiscount || 0));
                return Math.round(raw);
            },

            get cartRoundOff() {
                const raw = Math.max(0, this.cartSubtotal + this.cartTax - (this.orderDiscount || 0));
                return Number((this.cartGrandTotal - raw).toFixed(2));
            },

            get changeDue() {
                const tender = this.amountTendered !== null ? this.amountTendered : this.cartGrandTotal;
                return tender - this.cartGrandTotal;
            },

            roundUp(val, step) {
                return Math.ceil(val / step) * step;
            },

            onCustomerChange() {
                // Customer changed
            },

            async saveCustomer() {
                try {
                    const res = await fetch("{{ route('admin.pos.customers.store') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        },
                        body: JSON.stringify(this.newCustomer)
                    });
                    const data = await res.json();
                    if (data.success) {
                        this.customers.unshift(data.customer);
                        this.selectedCustomerId = data.customer.id;
                        this.showAddCustomer = false;
                        this.newCustomer = { name: '', phone: '', email: '', gstin: '', address: '' };
                    }
                } catch (err) {
                    alert('Error adding customer');
                }
            },

            async processCheckout() {
                if (this.cart.length === 0) return;
                this.isProcessing = true;

                const cust = this.customers.find(c => c.id == this.selectedCustomerId);

                const payload = {
                    customer_id: this.selectedCustomerId || null,
                    customer_name: cust ? cust.name : 'Walk-in Customer',
                    customer_phone: cust ? cust.phone : null,
                    customer_gstin: cust ? cust.gstin : null,
                    items: this.cart.map(i => ({
                        product_id: i.product_id,
                        quantity: i.quantity,
                        unit_price: i.unit_price,
                        batch_id: i.batch_id,
                    })),
                    discount_amount: this.orderDiscount || 0,
                    payment_method: this.paymentMethod,
                    amount_tendered: this.paymentMethod === 'cash' ? (this.amountTendered || this.cartGrandTotal) : this.cartGrandTotal,
                };

                try {
                    const res = await fetch("{{ route('admin.pos.checkout') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        },
                        body: JSON.stringify(payload)
                    });

                    const data = await res.json();

                    if (!res.ok) {
                        alert(data.message || 'Checkout failed. Please verify stock.');
                        this.isProcessing = false;
                        return;
                    }

                    if (data.success) {
                        this.completedSale = data;
                    }
                } catch (e) {
                    alert('Network error while processing checkout.');
                } finally {
                    this.isProcessing = false;
                }
            },

            resetCart() {
                this.cart = [];
                this.orderDiscount = 0;
                this.amountTendered = null;
                this.completedSale = null;
                this.selectedCustomerId = '';
                this.mobileTab = 'catalog';
                // Refetch products to update stock badges
                fetch("{{ route('admin.pos.products.search') }}")
                    .then(r => r.json())
                    .then(prods => {
                        this.allProducts = prods;
                        this.filterProducts();
                    });
            }
        };
    }
</script>
@endpush
@endsection
