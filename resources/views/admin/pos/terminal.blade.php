@extends('admin.layout')

@section('page-title', 'Point of Sale (POS)')

@section('content')
<div x-data="posTerminal()" x-init="init()" @keydown.window="handleShortcuts($event)" class="-m-4 lg:-m-6 flex flex-col h-[calc(100dvh-4.25rem)] lg:h-[calc(100vh-4.25rem)] bg-gray-100 overflow-hidden select-none">
    {{-- POS Header Bar --}}
    <header class="bg-white border-b border-gray-200 px-4 lg:px-6 py-2.5 sm:py-3 flex items-center justify-between flex-shrink-0 shadow-xs z-20">
        <div class="flex items-center gap-3">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 sm:w-9 sm:h-9 rounded-xl bg-brand-600 text-white flex items-center justify-center font-bold shadow-xs">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                </div>
                <div>
                    <h1 class="text-base sm:text-lg font-bold text-gray-900 leading-tight">POS Terminal</h1>
                    <p class="text-[11px] text-gray-500 hidden sm:block">Retail Counter Billing</p>
                </div>
            </div>
            <span class="hidden md:inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5 animate-pulse"></span>
                Multi-Batch & Split Pay Ready
            </span>
        </div>

        <div class="flex items-center gap-2 sm:gap-3">
            {{-- Held Carts Button --}}
            <button type="button"
                    @click="showHeldCarts = true"
                    x-show="heldCarts.length > 0"
                    class="inline-flex items-center px-2.5 sm:px-3 py-1.5 text-xs font-semibold text-amber-800 bg-amber-50 hover:bg-amber-100 border border-amber-200 rounded-xl transition">
                <svg class="w-4 h-4 sm:mr-1.5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span class="hidden sm:inline">Held Carts</span>
                <span class="ml-1 px-1.5 py-0.2 rounded-full text-[10px] font-extrabold bg-amber-600 text-white" x-text="heldCarts.length"></span>
            </button>

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
                           @keydown.enter.prevent="handleSearchEnter()"
                           placeholder="Scan barcode or search product / SKU (Press F2 or Enter)..."
                           class="w-full pl-9 pr-9 py-2 sm:py-2.5 bg-gray-100 border-none rounded-xl text-xs sm:text-sm text-gray-900 focus:bg-white focus:ring-2 focus:ring-brand-500 transition shadow-inner">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5 text-gray-400 absolute left-3 top-2.5 sm:top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <button type="button" x-show="searchQuery" @click="searchQuery = ''; filterProducts()" class="absolute right-3 top-2.5 sm:top-3 text-gray-400 hover:text-gray-600">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="flex items-center justify-between gap-2">
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
                    <span class="text-[11px] text-gray-400 hidden xl:inline">F2: Search | Ctrl+Enter: Pay</span>
                </div>
            </div>

            {{-- Products Grid --}}
            <div class="flex-1 overflow-y-auto p-3 sm:p-4 pb-24 lg:pb-4">
                <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-4 gap-2.5 sm:gap-3">
                    <template x-for="product in filteredProducts" :key="product.id">
                        <div @click="handleProductClick(product)"
                             class="bg-white rounded-xl border border-gray-200 p-2.5 sm:p-3.5 flex flex-col justify-between hover:border-brand-500 hover:shadow-md transition cursor-pointer group active:scale-[0.98]">
                            <div>
                                <div class="flex items-start justify-between gap-1 mb-1">
                                    <span class="text-[9px] sm:text-[10px] font-mono uppercase px-1.5 py-0.5 rounded bg-gray-100 text-gray-600 truncate max-w-[80px]" x-text="product.sku || 'SKU'"></span>
                                    <div class="flex items-center gap-1 flex-shrink-0">
                                        <span x-show="product.batches && product.batches.length > 1"
                                              class="text-[9px] font-bold px-1.5 py-0.5 rounded-full bg-indigo-50 text-indigo-700 border border-indigo-100"
                                              x-text="product.batches.length + ' Batches'"></span>
                                        <span class="text-[10px] sm:text-[11px] font-bold px-1.5 sm:px-2 py-0.5 rounded-full"
                                              :class="product.stock <= 0 ? 'bg-red-50 text-red-600' : 'bg-green-50 text-green-700'"
                                              x-text="product.stock + ' ' + product.unit"></span>
                                    </div>
                                </div>
                                <h3 class="text-xs sm:text-sm font-semibold text-gray-900 group-hover:text-brand-600 transition line-clamp-2 leading-snug" x-text="product.name"></h3>
                            </div>

                            <div class="mt-2.5 pt-2 border-t border-gray-100 flex items-center justify-between">
                                <div>
                                    <span class="text-sm sm:text-base font-bold text-gray-900" x-text="'₹' + getProductPriceDisplay(product)"></span>
                                    <span x-show="product.batches && product.batches.length > 1" class="text-[10px] text-indigo-600 font-medium block">Multi-price batch</span>
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
                    <p class="text-xs mt-1">Try another search term or barcode</p>
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
        <div class="w-full lg:w-[480px] xl:w-[520px] flex flex-col bg-white border-t lg:border-t-0 shadow-lg z-10 flex-shrink-0"
             :class="mobileTab === 'cart' ? 'flex' : 'hidden lg:flex'">

            {{-- Mobile Cart Header Navigation --}}
            <div class="lg:hidden px-4 py-2.5 bg-gray-50 border-b border-gray-200 flex items-center justify-between">
                <button type="button" @click="mobileTab = 'catalog'" class="text-xs font-bold text-brand-600 flex items-center gap-1 hover:text-brand-800">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    Add More Items
                </button>
                <div class="flex items-center gap-2">
                    <button type="button" x-show="cart.length > 0" @click="holdCurrentCart()" class="text-[11px] font-semibold text-amber-600 hover:text-amber-800">
                        Hold Cart
                    </button>
                    <button type="button" x-show="cart.length > 0" @click="clearCartConfirm()" class="text-[11px] font-semibold text-red-500 hover:text-red-700">
                        Clear
                    </button>
                </div>
            </div>

            {{-- Customer Selector Header --}}
            <div class="p-3 sm:p-3.5 border-b border-gray-200 bg-gray-50/80 space-y-2 flex-shrink-0">
                <div class="flex items-center justify-between gap-2">
                    <div class="flex-1 min-w-0">
                        <label class="block text-[10px] sm:text-[11px] font-semibold uppercase text-gray-500 tracking-wider mb-1">Customer / Orchardist</label>
                        <select x-model="selectedCustomerId" @change="onCustomerChange()" class="w-full text-xs font-medium bg-white border border-gray-200 rounded-lg px-2.5 py-1.5 focus:outline-none focus:border-brand-500">
                            <option value="">Walk-in Customer (Retail)</option>
                            <template x-for="cust in customers" :key="cust.id">
                                <option :value="cust.id" x-text="cust.name + (cust.phone ? ' (' + cust.phone + ')' : '') + (cust.outstanding_balance > 0 ? ' [Due: ₹' + Number(cust.outstanding_balance).toFixed(0) + ']' : '')"></option>
                            </template>
                        </select>
                    </div>
                    <div class="flex items-center gap-1.5 mt-4">
                        <button type="button" x-show="cart.length > 0" @click="holdCurrentCart()" title="Park current cart to serve next customer" class="hidden sm:inline-flex px-2.5 py-1.5 text-xs font-semibold text-amber-700 bg-amber-50 hover:bg-amber-100 rounded-lg border border-amber-200 transition items-center gap-1">
                            <svg class="w-3.5 h-3.5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Hold
                        </button>
                        <button type="button" @click="showAddCustomer = true" class="px-2.5 py-1.5 text-xs font-semibold text-brand-700 bg-brand-50 hover:bg-brand-100 rounded-lg border border-brand-200 transition flex items-center gap-1 flex-shrink-0">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            New
                        </button>
                    </div>
                </div>

                {{-- Outstanding Customer Balance Alert Banner --}}
                <div x-show="currentCustomerDue > 0" x-cloak class="bg-amber-50 border border-amber-200 rounded-xl p-2.5 flex items-center justify-between text-xs text-amber-900">
                    <div class="flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-amber-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        <span>Previous Outstanding Due: <strong class="font-extrabold text-amber-700" x-text="'₹' + currentCustomerDue.toFixed(2)"></strong></span>
                    </div>
                    <button type="button" @click="openSettleBalanceModal()" class="px-2 py-0.5 bg-amber-600 hover:bg-amber-700 text-white rounded font-bold text-[11px] shadow-xs transition">
                        Settle Balance
                    </button>
                </div>
            </div>

            {{-- Cart Items List --}}
            <div class="flex-1 overflow-y-auto p-3 sm:p-3.5 divide-y divide-gray-100 space-y-2">
                <template x-for="(item, idx) in cart" :key="item.cart_key">
                    <div class="pt-2 first:pt-0">
                        <div class="flex items-start justify-between gap-2">
                            <div class="flex-1 min-w-0">
                                <h4 class="text-xs font-semibold text-gray-900 truncate" x-text="item.name"></h4>
                                <div class="flex items-center flex-wrap gap-1.5 mt-0.5 text-[11px] text-gray-500">
                                    {{-- Editable Unit Price --}}
                                    <div class="flex items-center gap-1">
                                        <span>Rate: ₹</span>
                                        <input type="number"
                                               x-model.number="item.unit_price"
                                               step="1" min="0"
                                               class="w-16 text-center font-semibold text-gray-900 bg-gray-50 border border-gray-200 rounded px-1 py-0.5 text-xs focus:bg-white focus:border-brand-500">
                                        <span>/ <span x-text="item.unit"></span></span>
                                    </div>
                                    {{-- Batch Tag --}}
                                    <span x-show="item.batch_number"
                                          class="px-1.5 py-0.5 rounded text-[10px] font-mono bg-indigo-50 text-indigo-700 border border-indigo-100 flex items-center gap-1">
                                        <span x-text="'Batch: ' + item.batch_number"></span>
                                        <button type="button" x-show="item.has_multiple_batches" @click="changeItemBatch(item)" class="text-indigo-900 underline hover:font-bold">Switch</button>
                                    </span>
                                </div>
                            </div>
                            <div class="text-right flex items-center gap-1.5">
                                <span class="text-xs font-bold text-gray-900" x-text="'₹' + itemTotal(item).toFixed(2)"></span>
                                <button type="button" @click="removeFromCart(idx)" class="text-gray-400 hover:text-red-500 p-1 rounded hover:bg-gray-100">
                                    <svg class="w-4 h-4 text-gray-400 hover:text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </div>
                        </div>

                        {{-- Quantity Stepper --}}
                        <div class="flex items-center justify-between mt-2">
                            <div class="flex items-center border border-gray-200 rounded-lg bg-gray-50">
                                <button type="button" @click="updateQty(item, -1)" class="w-8 h-8 sm:w-7 sm:h-7 flex items-center justify-center text-gray-600 hover:bg-gray-200 active:bg-gray-300 rounded-l-lg transition font-bold text-base sm:text-sm">-</button>
                                <input type="number"
                                       x-model.number="item.quantity"
                                       @input="if(item.quantity < 0.01) item.quantity = 1"
                                       step="1" min="0.01"
                                       class="w-14 text-center text-xs font-bold bg-transparent border-0 focus:ring-0 p-0 text-gray-900">
                                <button type="button" @click="updateQty(item, 1)" class="w-8 h-8 sm:w-7 sm:h-7 flex items-center justify-center text-gray-600 hover:bg-gray-200 active:bg-gray-300 rounded-r-lg transition font-bold text-base sm:text-sm">+</button>
                            </div>
                            <span class="text-[10px] text-gray-400">Total: ₹<strong class="text-gray-800" x-text="itemTotal(item).toFixed(0)"></strong></span>
                        </div>
                    </div>
                </template>

                <div x-show="cart.length === 0" class="h-full flex flex-col items-center justify-center text-gray-400 py-12">
                    <svg class="w-12 h-12 text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    <p class="text-xs font-medium">Cart is empty</p>
                    <p class="text-[11px] text-gray-400 mt-0.5">Click products or scan barcode to add items</p>
                    <button type="button" @click="mobileTab = 'catalog'" class="lg:hidden mt-2 text-xs font-bold text-brand-600 underline">Browse Products</button>
                </div>
            </div>

            {{-- Cart Calculations & Payment Area (Zero Tax / Split Payment) --}}
            <div class="p-3.5 sm:p-4 bg-gray-50 border-t border-gray-200 space-y-2.5 sm:space-y-3 flex-shrink-0">
                <div class="space-y-1 text-xs text-gray-600">
                    <div class="flex justify-between">
                        <span>Items Subtotal</span>
                        <span class="font-medium text-gray-900" x-text="'₹' + cartSubtotal.toFixed(2)"></span>
                    </div>
                    <div class="flex items-center justify-between gap-2">
                        <span>Special Discount (₹)</span>
                        <input type="number" x-model.number="orderDiscount" min="0" step="1" placeholder="0" class="w-20 text-right text-xs bg-white border border-gray-200 rounded px-2 py-0.5 font-medium">
                    </div>
                    <div class="flex justify-between" x-show="cartRoundOff !== 0">
                        <span>Round Off</span>
                        <span class="text-gray-500" x-text="(cartRoundOff > 0 ? '+' : '') + cartRoundOff.toFixed(2)"></span>
                    </div>
                    <div class="flex justify-between pt-1.5 border-t border-gray-200 text-sm sm:text-base font-extrabold text-gray-900">
                        <span>Net Payable</span>
                        <span class="text-brand-700 text-base sm:text-lg" x-text="'₹' + cartGrandTotal.toFixed(0)"></span>
                    </div>
                </div>

                {{-- Payment Method Pills (Cash, UPI, Card, NetBank, Split) --}}
                <div>
                    <label class="block text-[10px] sm:text-[11px] font-semibold text-gray-500 uppercase tracking-wider mb-1">Payment Tender Mode</label>
                    <div class="grid grid-cols-5 gap-1 text-center">
                        <button type="button" @click="paymentMethod = 'cash'"
                                :class="paymentMethod === 'cash' ? 'bg-emerald-600 text-white font-bold shadow-xs' : 'bg-white text-gray-700 border border-gray-200'"
                                class="py-1.5 rounded-lg text-xs transition active:scale-95">Cash</button>
                        <button type="button" @click="paymentMethod = 'upi'"
                                :class="paymentMethod === 'upi' ? 'bg-indigo-600 text-white font-bold shadow-xs' : 'bg-white text-gray-700 border border-gray-200'"
                                class="py-1.5 rounded-lg text-xs transition active:scale-95">UPI</button>
                        <button type="button" @click="paymentMethod = 'card'"
                                :class="paymentMethod === 'card' ? 'bg-blue-600 text-white font-bold shadow-xs' : 'bg-white text-gray-700 border border-gray-200'"
                                class="py-1.5 rounded-lg text-xs transition active:scale-95">Card</button>
                        <button type="button" @click="paymentMethod = 'bank_transfer'"
                                :class="paymentMethod === 'bank_transfer' ? 'bg-gray-800 text-white font-bold shadow-xs' : 'bg-white text-gray-700 border border-gray-200'"
                                class="py-1.5 rounded-lg text-xs transition active:scale-95">Bank</button>
                        <button type="button" @click="paymentMethod = 'split'"
                                :class="paymentMethod === 'split' ? 'bg-purple-600 text-white font-bold shadow-xs' : 'bg-white text-purple-700 border border-purple-200 font-semibold'"
                                class="py-1.5 rounded-lg text-xs transition active:scale-95">Split</button>
                    </div>
                </div>

                {{-- Single Cash Tender Section --}}
                <div x-show="paymentMethod === 'cash'" class="bg-white p-2.5 rounded-xl border border-gray-200 space-y-2">
                    <div class="flex items-center justify-between gap-2">
                        <span class="text-xs font-semibold text-gray-700">Cash Received:</span>
                        <input type="number" x-model.number="amountTendered" :placeholder="cartGrandTotal" class="w-28 text-right font-bold text-sm bg-gray-50 border border-gray-200 rounded-lg px-2.5 py-1">
                    </div>

                    {{-- Quick Cash Chips --}}
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

                    {{-- Change / Remaining Due Indicator --}}
                    <div class="text-xs pt-1 border-t border-gray-100">
                        <template x-if="cashPaidDue > 0">
                            <div class="flex items-center justify-between text-amber-700 font-semibold">
                                <span>Partial Pay! Balance Due:</span>
                                <span>₹<span x-text="cashPaidDue.toFixed(0)"></span> (Goes to Balance)</span>
                            </div>
                        </template>
                        <template x-if="cashPaidDue <= 0">
                            <div class="flex items-center justify-between text-emerald-600 font-bold">
                                <span class="text-gray-500 font-normal">Change Return:</span>
                                <span>₹<span x-text="changeDue.toFixed(0)"></span></span>
                            </div>
                        </template>
                    </div>
                </div>

                {{-- Single Digital / Bank Tender Section with Partial Payment Support --}}
                <div x-show="['upi', 'card', 'bank_transfer'].includes(paymentMethod)" class="bg-white p-2.5 rounded-xl border border-gray-200 space-y-2">
                    <div class="flex items-center justify-between gap-2">
                        <span class="text-xs font-semibold text-gray-700">Amount Paid (<span x-text="paymentMethod.toUpperCase()"></span>):</span>
                        <input type="number" x-model.number="digitalAmountPaid" :placeholder="cartGrandTotal" class="w-28 text-right font-bold text-sm bg-gray-50 border border-gray-200 rounded-lg px-2.5 py-1">
                    </div>
                    <div x-show="digitalPaidDue > 0" class="flex items-center justify-between text-xs text-amber-700 font-semibold pt-1 border-t border-gray-100">
                        <span>Partial Pay! Balance Due:</span>
                        <span>₹<span x-text="digitalPaidDue.toFixed(0)"></span> (Goes to Balance)</span>
                    </div>
                </div>

                {{-- Split / Multi-Payment Section --}}
                <div x-show="paymentMethod === 'split'" class="bg-purple-50/60 p-3 rounded-xl border border-purple-200 space-y-2">
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-xs font-bold text-purple-900">Multi-Tender Breakdown</span>
                        <button type="button" @click="split5050()" class="text-[10px] font-semibold text-purple-700 bg-purple-100 px-2 py-0.5 rounded hover:bg-purple-200">
                            Split 50/50 Cash & UPI
                        </button>
                    </div>

                    <div class="grid grid-cols-2 gap-2 text-xs">
                        <div>
                            <label class="block text-[10px] font-semibold text-gray-600 mb-0.5">Cash (₹)</label>
                            <input type="number" x-model.number="splitCash" min="0" placeholder="0" class="w-full text-right font-bold text-xs bg-white border border-gray-200 rounded-lg px-2.5 py-1.5 focus:border-purple-500">
                        </div>
                        <div>
                            <label class="block text-[10px] font-semibold text-gray-600 mb-0.5">UPI / QR (₹)</label>
                            <input type="number" x-model.number="splitUpi" min="0" placeholder="0" class="w-full text-right font-bold text-xs bg-white border border-gray-200 rounded-lg px-2.5 py-1.5 focus:border-purple-500">
                        </div>
                        <div>
                            <label class="block text-[10px] font-semibold text-gray-600 mb-0.5">Card (₹)</label>
                            <input type="number" x-model.number="splitCard" min="0" placeholder="0" class="w-full text-right font-bold text-xs bg-white border border-gray-200 rounded-lg px-2.5 py-1.5 focus:border-purple-500">
                        </div>
                        <div>
                            <label class="block text-[10px] font-semibold text-gray-600 mb-0.5">Bank / Other (₹)</label>
                            <input type="number" x-model.number="splitBank" min="0" placeholder="0" class="w-full text-right font-bold text-xs bg-white border border-gray-200 rounded-lg px-2.5 py-1.5 focus:border-purple-500">
                        </div>
                    </div>

                    {{-- Live Split Balance Tracking --}}
                    <div class="pt-2 border-t border-purple-200/80 space-y-1 text-xs">
                        <div class="flex justify-between font-medium text-gray-700">
                            <span>Total Paying Now:</span>
                            <span class="font-bold text-purple-900" x-text="'₹' + splitTotalPaid.toFixed(0)"></span>
                        </div>
                        <template x-if="splitRemainingDue > 0">
                            <div class="flex justify-between font-bold text-amber-700 bg-amber-50 px-2 py-1 rounded border border-amber-200">
                                <span>Remaining Balance Due:</span>
                                <span>₹<span x-text="splitRemainingDue.toFixed(0)"></span> (Recorded in Balance)</span>
                            </div>
                        </template>
                        <template x-if="splitRemainingDue <= 0 && splitChange > 0">
                            <div class="flex justify-between font-bold text-emerald-700">
                                <span>Change Return (from Cash):</span>
                                <span>₹<span x-text="splitChange.toFixed(0)"></span></span>
                            </div>
                        </template>
                        <div class="flex items-center gap-1.5 pt-1 text-[10px]">
                            <button type="button" @click="fillRemainingWith('upi')" class="text-purple-700 underline font-semibold">Fill Remaining in UPI</button>
                            <span class="text-gray-300">·</span>
                            <button type="button" @click="fillRemainingWith('cash')" class="text-purple-700 underline font-semibold">Fill Remaining in Cash</button>
                            <span class="text-gray-300">·</span>
                            <button type="button" @click="clearSplit()" class="text-red-500 underline font-semibold">Clear</button>
                        </div>
                    </div>
                </div>

                {{-- Complete Sale Button --}}
                <button type="button"
                        @click="processCheckout()"
                        :disabled="cart.length === 0 || isProcessing"
                        class="w-full py-3.5 px-4 rounded-xl font-bold text-sm text-white bg-brand-600 hover:bg-brand-700 active:scale-[0.99] disabled:opacity-50 disabled:cursor-not-allowed shadow-md transition flex items-center justify-center gap-2">
                    <span x-show="!isProcessing" class="flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        <span>Complete Sale (<span x-text="'₹' + cartGrandTotal.toFixed(0)"></span>)</span>
                    </span>
                    <span x-show="isProcessing" class="flex items-center gap-2">
                        <svg class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                        Processing Sale...
                    </span>
                </button>
            </div>
        </div>
    </div>

    {{-- Batch Selection Modal (When product has multiple batches with different pricing) --}}
    <div x-show="batchPickerProduct" x-cloak class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-3 sm:p-4 bg-black/60 backdrop-blur-sm">
        <div class="bg-white rounded-2xl max-w-lg w-full p-5 sm:p-6 space-y-4 shadow-2xl animate-in fade-in duration-200" @click.away="batchPickerProduct = null">
            <div class="flex items-start justify-between pb-3 border-b border-gray-100">
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-brand-700">Select Batch & Price</span>
                    <h3 class="text-base font-bold text-gray-900 mt-0.5" x-text="batchPickerProduct?.name"></h3>
                    <p class="text-xs text-gray-500 font-mono" x-text="batchPickerProduct?.sku"></p>
                </div>
                <button type="button" @click="batchPickerProduct = null" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <p class="text-xs text-gray-500">This product has multiple inventory batches with specific rates. Choose the batch to sell from:</p>

            <div class="space-y-2 max-h-72 overflow-y-auto pr-1">
                <template x-for="b in (batchPickerProduct?.batches || [])" :key="b.id">
                    <div class="p-3 rounded-xl border border-gray-200 hover:border-brand-500 hover:bg-brand-50/40 transition flex items-center justify-between gap-3">
                        <div class="space-y-0.5">
                            <div class="flex items-center gap-2">
                                <span class="font-mono font-bold text-xs text-gray-900" x-text="b.batch_number"></span>
                                <span x-show="b.is_near_expiry" class="px-1.5 py-0.2 rounded text-[9px] font-semibold bg-amber-100 text-amber-800">Near Expiry</span>
                            </div>
                            <div class="text-[11px] text-gray-500 flex items-center gap-3">
                                <span>Avail: <strong class="text-emerald-700" x-text="b.current_qty + ' ' + batchPickerProduct.unit"></strong></span>
                                <span x-show="b.expiry">Exp: <span x-text="b.expiry"></span></span>
                            </div>
                        </div>

                        <div class="flex items-center gap-3">
                            <div class="text-right">
                                <span class="text-sm font-extrabold text-gray-900 block" x-text="'₹' + Number(b.selling_price).toFixed(0)"></span>
                                <span class="text-[10px] text-gray-400">per <span x-text="batchPickerProduct.unit"></span></span>
                            </div>
                            <button type="button"
                                    @click="addBatchToCart(batchPickerProduct, b)"
                                    class="px-3 py-1.5 bg-brand-600 hover:bg-brand-700 text-white rounded-lg text-xs font-bold transition shadow-xs">
                                Select
                            </button>
                        </div>
                    </div>
                </template>
            </div>

            <div class="flex justify-end pt-2 border-t border-gray-100">
                <button type="button" @click="batchPickerProduct = null" class="px-4 py-2 text-xs font-semibold text-gray-600 hover:bg-gray-100 rounded-xl">
                    Cancel
                </button>
            </div>
        </div>
    </div>

    {{-- Held Carts Modal --}}
    <div x-show="showHeldCarts" x-cloak class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-3 sm:p-4 bg-black/60 backdrop-blur-sm">
        <div class="bg-white rounded-2xl max-w-md w-full p-5 sm:p-6 space-y-4 shadow-2xl" @click.away="showHeldCarts = false">
            <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                <h3 class="text-base font-bold text-gray-900">Held (Parked) Carts</h3>
                <button type="button" @click="showHeldCarts = false" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="space-y-2.5 max-h-80 overflow-y-auto">
                <template x-for="(hc, idx) in heldCarts" :key="hc.id">
                    <div class="p-3 rounded-xl border border-gray-200 bg-gray-50 flex items-center justify-between gap-3">
                        <div>
                            <p class="text-xs font-bold text-gray-900" x-text="hc.customer_name"></p>
                            <p class="text-[11px] text-gray-500" x-text="hc.items.length + ' items · ₹' + hc.total.toFixed(0) + ' · ' + hc.time"></p>
                        </div>
                        <div class="flex items-center gap-2">
                            <button type="button" @click="resumeHeldCart(idx)" class="px-3 py-1.5 bg-brand-600 hover:bg-brand-700 text-white rounded-lg text-xs font-bold">
                                Resume
                            </button>
                            <button type="button" @click="deleteHeldCart(idx)" class="p-1.5 text-gray-400 hover:text-red-600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>

    {{-- Settle Balance Modal --}}
    <div x-show="showSettleBalanceModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-3 sm:p-4 bg-black/60 backdrop-blur-sm">
        <div class="bg-white rounded-2xl max-w-sm sm:max-w-md w-full p-5 sm:p-6 space-y-4 shadow-2xl" @click.away="showSettleBalanceModal = false">
            <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                <h3 class="text-base font-bold text-gray-900">Settle Customer Balance</h3>
                <button type="button" @click="showSettleBalanceModal = false" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="bg-amber-50 p-3 rounded-xl border border-amber-200 text-xs text-amber-900">
                <p>Customer: <strong class="font-bold" x-text="selectedCustomer?.name"></strong></p>
                <p class="mt-1">Current Outstanding Due: <strong class="text-base text-amber-700" x-text="'₹' + currentCustomerDue.toFixed(2)"></strong></p>
            </div>

            <form @submit.prevent="submitSettleBalance()" class="space-y-3 text-xs">
                <div>
                    <label class="block font-semibold text-gray-700 mb-1">Payment Amount (₹) <span class="text-red-500">*</span></label>
                    <input type="number" x-model.number="settleAmount" :max="currentCustomerDue" step="1" required class="w-full rounded-xl border border-gray-200 px-3 py-2 text-sm font-bold focus:border-brand-500">
                </div>
                <div>
                    <label class="block font-semibold text-gray-700 mb-1">Payment Mode <span class="text-red-500">*</span></label>
                    <select x-model="settlePaymentMethod" required class="w-full rounded-xl border border-gray-200 px-3 py-2 text-sm focus:border-brand-500">
                        <option value="cash">Cash</option>
                        <option value="upi">UPI / Online QR</option>
                        <option value="card">Card</option>
                        <option value="bank_transfer">Bank Transfer</option>
                    </select>
                </div>
                <div>
                    <label class="block font-semibold text-gray-700 mb-1">Receipt / Remarks Note</label>
                    <input type="text" x-model="settleNote" placeholder="e.g. Settle old fertilizer debt" class="w-full rounded-xl border border-gray-200 px-3 py-2 text-sm focus:border-brand-500">
                </div>

                <div class="flex justify-end gap-2 pt-3">
                    <button type="button" @click="showSettleBalanceModal = false" class="px-4 py-2 rounded-xl text-gray-600 hover:bg-gray-100 font-semibold">Cancel</button>
                    <button type="submit" class="px-4 py-2 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-bold">Record Payment</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Sale Completed Modal --}}
    <div x-show="completedSale" x-cloak class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-3 sm:p-4 bg-black/60 backdrop-blur-sm">
        <div class="bg-white rounded-2xl max-w-sm sm:max-w-md w-full p-5 sm:p-6 text-center space-y-4 shadow-2xl animate-in fade-in zoom-in duration-200">
            <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center mx-auto">
                <svg class="w-7 h-7 sm:w-8 sm:h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
            </div>
            <div>
                <h3 class="text-lg sm:text-xl font-bold text-gray-900">Sale Completed!</h3>
                <p class="text-xs sm:text-sm text-gray-500 mt-1">Invoice: <strong class="text-gray-900 font-mono" x-text="completedSale?.invoice_number"></strong></p>
                <div class="mt-2 py-2 px-3 bg-gray-50 rounded-xl space-y-1">
                    <p class="text-sm font-semibold text-gray-600">Total Bill: <strong class="text-gray-900" x-text="'₹' + completedSale?.grand_total"></strong></p>
                    <p class="text-xs text-emerald-600 font-semibold">Amount Paid: <strong x-text="'₹' + completedSale?.amount_paid"></strong></p>
                    <template x-if="completedSale?.balance_due > 0">
                        <p class="text-xs text-amber-700 font-bold bg-amber-100 px-2 py-0.5 rounded">Added to Customer Due: ₹<span x-text="completedSale?.balance_due"></span></p>
                    </template>
                </div>
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
                <h3 class="text-base font-bold text-gray-900">Add POS Customer / Orchardist</h3>
                <button type="button" @click="showAddCustomer = false" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form @submit.prevent="saveCustomer()" class="space-y-3 text-xs">
                <div>
                    <label class="block font-semibold text-gray-700 mb-1">Customer / Farmer Name <span class="text-red-500">*</span></label>
                    <input type="text" x-model="newCustomer.name" required class="w-full rounded-xl border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:ring-1 focus:ring-brand-500">
                </div>
                <div>
                    <label class="block font-semibold text-gray-700 mb-1">Phone Number</label>
                    <input type="text" x-model="newCustomer.phone" class="w-full rounded-xl border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:ring-1 focus:ring-brand-500">
                </div>
                <div>
                    <label class="block font-semibold text-gray-700 mb-1">Address / Village</label>
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
                'batches' => $p->activeBatchesFifo->map(fn ($b) => [
                    'id' => $b->id,
                    'batch_number' => $b->batch_number,
                    'lot_number' => $b->lot_number,
                    'current_qty' => (float) $b->current_qty,
                    'unit_cost' => (float) ($b->unit_cost ?? 0),
                    'selling_price' => $b->effectiveSellingPrice(),
                    'expiry' => $b->expiry_date?->format('d M Y'),
                    'is_near_expiry' => $b->isNearExpiry(60),
                ])->values(),
            ])->values()),
            filteredProducts: [],
            searchQuery: '',
            filterType: 'all',
            customers: @js($customers),
            selectedCustomerId: '',
            cart: [],
            orderDiscount: 0,
            paymentMethod: 'cash', // 'cash', 'upi', 'card', 'bank_transfer', 'split'
            amountTendered: null,
            digitalAmountPaid: null,
            // Split tender amounts
            splitCash: 0,
            splitUpi: 0,
            splitCard: 0,
            splitBank: 0,
            // Modals & state
            isProcessing: false,
            completedSale: null,
            batchPickerProduct: null,
            showHeldCarts: false,
            heldCarts: [],
            showAddCustomer: false,
            newCustomer: { name: '', phone: '', email: '', gstin: '', address: '' },
            showSettleBalanceModal: false,
            settleAmount: 0,
            settlePaymentMethod: 'cash',
            settleNote: '',
            clockTime: '',
            mobileTab: 'catalog',

            init() {
                this.filteredProducts = this.allProducts;
                this.updateClock();
                setInterval(() => this.updateClock(), 1000);
            },

            updateClock() {
                const now = new Date();
                this.clockTime = now.toLocaleDateString('en-IN', { day: '2-digit', month: 'short' }) + ' ' + now.toLocaleTimeString('en-IN', { hour: '2-digit', minute: '2-digit' });
            },

            handleShortcuts(e) {
                if (e.key === 'F2') {
                    e.preventDefault();
                    this.$refs.searchInput?.focus();
                } else if (e.key === 'Escape') {
                    this.batchPickerProduct = null;
                    this.showAddCustomer = false;
                    this.showHeldCarts = false;
                    this.showSettleBalanceModal = false;
                } else if (e.ctrlKey && e.key === 'Enter') {
                    e.preventDefault();
                    this.processCheckout();
                }
            },

            filterProducts() {
                const q = this.searchQuery.toLowerCase().trim();
                this.filteredProducts = this.allProducts.filter(p => {
                    const matchesType = this.filterType === 'all' || p.type === this.filterType;
                    const matchesQuery = !q || p.name.toLowerCase().includes(q) || (p.sku && p.sku.toLowerCase().includes(q));
                    return matchesType && matchesQuery;
                });
            },

            handleSearchEnter() {
                if (this.filteredProducts.length === 1) {
                    this.handleProductClick(this.filteredProducts[0]);
                    this.searchQuery = '';
                    this.filterProducts();
                }
            },

            getProductPriceDisplay(product) {
                if (product.batches && product.batches.length > 1) {
                    const prices = product.batches.map(b => b.selling_price);
                    const min = Math.min(...prices);
                    const max = Math.max(...prices);
                    if (min !== max) {
                        return min.toFixed(0) + ' - ' + max.toFixed(0);
                    }
                }
                return Number(product.rate).toFixed(0);
            },

            handleProductClick(product) {
                // If product has multiple active batches with distinct pricing/lots, open picker modal
                if (product.batches && product.batches.length > 1) {
                    this.batchPickerProduct = product;
                } else if (product.batches && product.batches.length === 1) {
                    const singleBatch = product.batches[0];
                    this.addBatchToCart(product, singleBatch);
                } else {
                    this.addBatchToCart(product, null);
                }
            },

            addBatchToCart(product, batch) {
                const batchId = batch ? batch.id : null;
                const batchNumber = batch ? batch.batch_number : null;
                const price = batch ? batch.selling_price : product.rate;
                const cost = batch ? batch.unit_cost : product.cost;
                const cartKey = `${product.id}_${batchId || 'auto'}`;

                const existing = this.cart.find(i => i.cart_key === cartKey);
                if (existing) {
                    existing.quantity += 1;
                } else {
                    this.cart.push({
                        cart_key: cartKey,
                        product_id: product.id,
                        batch_id: batchId,
                        batch_number: batchNumber,
                        has_multiple_batches: (product.batches && product.batches.length > 1),
                        name: product.name,
                        unit: product.unit,
                        unit_price: price,
                        cost_price: cost,
                        quantity: 1,
                        product_ref: product,
                    });
                }
                this.batchPickerProduct = null;
            },

            changeItemBatch(item) {
                if (item.product_ref) {
                    this.batchPickerProduct = item.product_ref;
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
                return item.unit_price * item.quantity;
            },

            get cartTotalQty() {
                return this.cart.reduce((sum, item) => sum + item.quantity, 0);
            },

            get cartSubtotal() {
                return this.cart.reduce((sum, item) => sum + (item.unit_price * item.quantity), 0);
            },

            get cartGrandTotal() {
                const raw = Math.max(0, this.cartSubtotal - (this.orderDiscount || 0));
                return Math.round(raw);
            },

            get cartRoundOff() {
                const raw = Math.max(0, this.cartSubtotal - (this.orderDiscount || 0));
                return Number((this.cartGrandTotal - raw).toFixed(2));
            },

            get selectedCustomer() {
                return this.customers.find(c => c.id == this.selectedCustomerId) || null;
            },

            get currentCustomerDue() {
                return this.selectedCustomer ? Number(this.selectedCustomer.outstanding_balance || 0) : 0;
            },

            get cashPaidDue() {
                const tender = this.amountTendered !== null ? this.amountTendered : this.cartGrandTotal;
                return Math.max(0, this.cartGrandTotal - tender);
            },

            get changeDue() {
                const tender = this.amountTendered !== null ? this.amountTendered : this.cartGrandTotal;
                return Math.max(0, tender - this.cartGrandTotal);
            },

            get digitalPaidDue() {
                const paid = this.digitalAmountPaid !== null ? this.digitalAmountPaid : this.cartGrandTotal;
                return Math.max(0, this.cartGrandTotal - paid);
            },

            get splitTotalPaid() {
                return Number(this.splitCash || 0) + Number(this.splitUpi || 0) + Number(this.splitCard || 0) + Number(this.splitBank || 0);
            },

            get splitRemainingDue() {
                return Math.max(0, this.cartGrandTotal - this.splitTotalPaid);
            },

            get splitChange() {
                if (this.splitTotalPaid > this.cartGrandTotal) {
                    return this.splitTotalPaid - this.cartGrandTotal;
                }
                return 0;
            },

            split5050() {
                const half = Math.round(this.cartGrandTotal / 2);
                this.splitCash = half;
                this.splitUpi = this.cartGrandTotal - half;
                this.splitCard = 0;
                this.splitBank = 0;
            },

            fillRemainingWith(mode) {
                const otherTotal = (mode === 'upi' ? (Number(this.splitCash || 0) + Number(this.splitCard || 0) + Number(this.splitBank || 0)) : (Number(this.splitUpi || 0) + Number(this.splitCard || 0) + Number(this.splitBank || 0)));
                const remaining = Math.max(0, this.cartGrandTotal - otherTotal);
                if (mode === 'upi') {
                    this.splitUpi = remaining;
                } else if (mode === 'cash') {
                    this.splitCash = remaining;
                }
            },

            clearSplit() {
                this.splitCash = 0;
                this.splitUpi = 0;
                this.splitCard = 0;
                this.splitBank = 0;
            },

            roundUp(val, step) {
                return Math.ceil(val / step) * step;
            },

            onCustomerChange() {
                // Customer changed
            },

            holdCurrentCart() {
                if (this.cart.length === 0) return;
                const cust = this.selectedCustomer;
                this.heldCarts.push({
                    id: Date.now(),
                    customer_id: this.selectedCustomerId,
                    customer_name: cust ? cust.name : 'Walk-in Customer',
                    items: [...this.cart],
                    discount: this.orderDiscount,
                    total: this.cartGrandTotal,
                    time: new Date().toLocaleTimeString('en-IN', { hour: '2-digit', minute: '2-digit' }),
                });
                this.cart = [];
                this.orderDiscount = 0;
                this.selectedCustomerId = '';
                this.mobileTab = 'catalog';
            },

            resumeHeldCart(idx) {
                const hc = this.heldCarts[idx];
                this.cart = hc.items;
                this.selectedCustomerId = hc.customer_id;
                this.orderDiscount = hc.discount;
                this.heldCarts.splice(idx, 1);
                this.showHeldCarts = false;
                this.mobileTab = 'cart';
            },

            deleteHeldCart(idx) {
                this.heldCarts.splice(idx, 1);
            },

            openSettleBalanceModal() {
                this.settleAmount = this.currentCustomerDue;
                this.showSettleBalanceModal = true;
            },

            async submitSettleBalance() {
                if (!this.selectedCustomer || this.settleAmount <= 0) return;
                try {
                    const res = await fetch(`/admin/pos/customers/${this.selectedCustomer.id}/settle-balance`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        },
                        body: JSON.stringify({
                            amount: this.settleAmount,
                            payment_method: this.settlePaymentMethod,
                            note: this.settleNote,
                        })
                    });
                    const data = await res.json();
                    if (data.success) {
                        this.selectedCustomer.outstanding_balance = data.new_balance;
                        this.showSettleBalanceModal = false;
                        alert(data.message);
                    } else {
                        alert(data.message || 'Error settling balance');
                    }
                } catch (e) {
                    alert('Network error recording balance payment.');
                }
            },

            async saveCustomer() {
                try {
                    const res = await fetch("{{ route('admin.pos.customers.store') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
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

                const cust = this.selectedCustomer;

                let payments = [];
                let amountTendered = 0;
                let amountPaid = 0;

                if (this.paymentMethod === 'split') {
                    if (Number(this.splitCash) > 0) payments.push({ method: 'cash', amount: Number(this.splitCash) });
                    if (Number(this.splitUpi) > 0) payments.push({ method: 'upi', amount: Number(this.splitUpi) });
                    if (Number(this.splitCard) > 0) payments.push({ method: 'card', amount: Number(this.splitCard) });
                    if (Number(this.splitBank) > 0) payments.push({ method: 'bank_transfer', amount: Number(this.splitBank) });
                    amountPaid = this.splitTotalPaid;
                    amountTendered = Number(this.splitCash || 0);
                } else if (this.paymentMethod === 'cash') {
                    amountTendered = this.amountTendered !== null ? Number(this.amountTendered) : this.cartGrandTotal;
                    amountPaid = Math.min(amountTendered, this.cartGrandTotal);
                } else {
                    amountPaid = this.digitalAmountPaid !== null ? Number(this.digitalAmountPaid) : this.cartGrandTotal;
                    amountTendered = amountPaid;
                }

                // If balance is due, ensure a customer is selected or name is given
                const balanceDue = Math.max(0, this.cartGrandTotal - amountPaid);
                if (balanceDue > 0 && !this.selectedCustomerId && (!cust || cust.name === 'Walk-in Customer')) {
                    alert('An unpaid balance of ₹' + balanceDue.toFixed(0) + ' is remaining. Please select or add a named Customer so this debt is recorded.');
                    this.isProcessing = false;
                    return;
                }

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
                    payments: this.paymentMethod === 'split' ? payments : null,
                    amount_tendered: amountTendered,
                    amount_paid: amountPaid,
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
                        if (cust && data.balance_due > 0) {
                            cust.outstanding_balance = (Number(cust.outstanding_balance) || 0) + Number(data.balance_due);
                        }
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
                this.digitalAmountPaid = null;
                this.clearSplit();
                this.completedSale = null;
                this.selectedCustomerId = '';
                this.mobileTab = 'catalog';
                // Refresh products stock and batches
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
