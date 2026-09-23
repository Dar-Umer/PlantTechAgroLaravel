@extends('admin.layout')

@section('page-title', 'Point of Sale (POS)')

@section('content')
<div x-data="posTerminal()" x-init="init()" @keydown.window="handleShortcuts($event)"
     class="-m-4 lg:-m-6 flex flex-col h-[calc(100dvh-5rem)] bg-slate-100 overflow-hidden select-none font-sans text-slate-800">

    {{-- POS Header Bar --}}
    <header class="bg-white border-b border-slate-200 px-3 sm:px-6 py-2.5 sm:py-3 flex items-center justify-between flex-shrink-0 shadow-xs z-20">
        {{-- Left: Brand & Status --}}
        <div class="flex items-center gap-2.5 sm:gap-3">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 sm:w-9 sm:h-9 rounded-xl bg-gradient-to-br from-brand-500 to-brand-700 text-white flex items-center justify-center font-bold shadow-sm">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                </div>
                <div>
                    <h1 class="text-sm sm:text-base font-bold text-slate-900 leading-tight">POS Terminal</h1>
                    <p class="text-[10px] sm:text-[11px] text-slate-500 hidden xs:block">Fast Retail Counter Billing</p>
                </div>
            </div>
            <span class="hidden md:inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200/80">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5 animate-pulse"></span>
                Multi-Batch & Split Pay Ready
            </span>
        </div>

        {{-- Right Controls: Mobile Tabs, Shortcuts, Held Carts, Sales List --}}
        <div class="flex items-center gap-1.5 sm:gap-3">
            {{-- Mobile Segmented Tab Switcher --}}
            <div class="lg:hidden flex items-center p-1 bg-slate-100 rounded-xl border border-slate-200">
                <button type="button"
                        @click="mobileTab = 'catalog'"
                        :class="mobileTab === 'catalog' ? 'bg-white text-slate-900 font-bold shadow-xs' : 'text-slate-500 font-medium'"
                        class="px-3 py-1.5 text-xs rounded-lg transition-all flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                    <span>Items</span>
                </button>
                <button type="button"
                        @click="mobileTab = 'cart'"
                        :class="mobileTab === 'cart' ? 'bg-brand-600 text-white font-bold shadow-xs' : 'text-slate-600 font-medium'"
                        class="px-3 py-1.5 text-xs rounded-lg transition-all flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    <span>Cart</span>
                    <span x-show="cartTotalQty > 0"
                          :class="mobileTab === 'cart' ? 'bg-white text-brand-700' : 'bg-brand-600 text-white'"
                          class="px-1.5 py-0.2 rounded-full text-[10px] font-extrabold"
                          x-text="cartTotalQty"></span>
                </button>
            </div>

            {{-- Held Carts Button --}}
            <button type="button"
                    @click="showHeldCarts = true"
                    x-show="heldCarts.length > 0"
                    class="inline-flex items-center px-2 sm:px-3 py-1.5 text-xs font-semibold text-amber-800 bg-amber-50 hover:bg-amber-100 border border-amber-200 rounded-xl transition shadow-2xs">
                <svg class="w-3.5 h-3.5 sm:mr-1.5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span class="hidden sm:inline">Held Carts</span>
                <span class="ml-1 px-1.5 py-0.2 rounded-full text-[10px] font-extrabold bg-amber-600 text-white" x-text="heldCarts.length"></span>
            </button>

            {{-- Invoices History Link --}}
            <a href="{{ route('admin.pos.sales') }}"
               class="inline-flex items-center px-2.5 sm:px-3 py-1.5 sm:py-2 text-xs font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-xl transition">
                <svg class="w-3.5 h-3.5 sm:mr-1.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                <span class="hidden sm:inline">Sales Invoices</span>
            </a>

            {{-- Cashier Time --}}
            <div class="text-right hidden sm:block pl-2 border-l border-slate-200">
                <p class="text-xs font-semibold text-slate-900 leading-tight">{{ auth('admin')->user()->name }}</p>
                <p class="text-[11px] text-slate-400" x-text="clockTime"></p>
            </div>
        </div>
    </header>

    {{-- Main POS Work Area: Responsive Catalog & Cart --}}
    <div class="flex-1 flex flex-col lg:flex-row overflow-hidden relative">

        {{-- Product Catalog (Full screen on mobile when tab = 'catalog', 60% on desktop) --}}
        <div class="flex-1 flex flex-col border-r border-slate-200 bg-slate-50 overflow-hidden"
             :class="mobileTab === 'catalog' ? 'flex' : 'hidden lg:flex'">

            {{-- Search & Category Filter Header --}}
            <div class="p-3 sm:p-4 bg-white border-b border-slate-200 space-y-2.5 sm:space-y-3 flex-shrink-0 shadow-2xs">
                {{-- Search Bar --}}
                <div class="relative">
                    <input type="text"
                           x-ref="searchInput"
                           x-model="searchQuery"
                           @input="filterProducts()"
                           @keydown.enter.prevent="handleSearchEnter()"
                           placeholder="Scan barcode or search by name, SKU (Press F2 or Enter)..."
                           class="w-full pl-10 pr-9 py-2 sm:py-2.5 bg-slate-100 hover:bg-slate-50 focus:bg-white border border-transparent focus:border-brand-500 rounded-xl text-xs sm:text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-brand-100 transition shadow-inner">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5 text-slate-400 absolute left-3.5 top-2.5 sm:top-3 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <button type="button" x-show="searchQuery" @click="searchQuery = ''; filterProducts()" class="absolute right-3 top-2.5 sm:top-3 text-slate-400 hover:text-slate-600">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                {{-- Category & Type Filter Pills --}}
                <div class="flex items-center justify-between gap-2">
                    <div class="flex items-center gap-1.5 sm:gap-2 overflow-x-auto pb-0.5 text-xs scrollbar-none">
                        <button type="button"
                                @click="filterType = 'all'; filterProducts()"
                                :class="filterType === 'all' ? 'bg-brand-600 text-white font-bold shadow-xs' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                                class="px-3 py-1.5 rounded-xl font-medium transition flex-shrink-0">
                            All Items (<span x-text="allProducts.length"></span>)
                        </button>
                        <button type="button"
                                @click="filterType = 'sellable'; filterProducts()"
                                :class="filterType === 'sellable' ? 'bg-brand-600 text-white font-bold shadow-xs' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                                class="px-3 py-1.5 rounded-xl font-medium transition flex-shrink-0">
                            Retail Products
                        </button>
                        <button type="button"
                                @click="filterType = 'material'; filterProducts()"
                                :class="filterType === 'material' ? 'bg-brand-600 text-white font-bold shadow-xs' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                                class="px-3 py-1.5 rounded-xl font-medium transition flex-shrink-0">
                            Materials
                        </button>
                    </div>
                    <span class="text-[11px] text-slate-400 hidden xl:inline font-mono">F2: Search · Enter: Auto-Add</span>
                </div>
            </div>

            {{-- Products Grid --}}
            <div class="flex-1 overflow-y-auto p-3 sm:p-4 pb-24 lg:pb-4">
                <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5 gap-2.5 sm:gap-3.5">
                    <template x-for="product in filteredProducts" :key="product.id">
                        <div @click="handleProductClick(product)"
                             class="bg-white rounded-2xl border border-slate-200 hover:border-brand-500 hover:shadow-md transition-all duration-150 p-2.5 sm:p-3 flex flex-col justify-between cursor-pointer group active:scale-[0.98] relative overflow-hidden">

                            {{-- Product Top Header: SKU & Stock Badge --}}
                            <div>
                                <div class="flex items-center justify-between gap-1 mb-1.5">
                                    <span class="text-[9px] sm:text-[10px] font-mono uppercase px-1.5 py-0.5 rounded bg-slate-100 text-slate-600 truncate max-w-[85px]" x-text="product.sku || 'SKU'"></span>
                                    <span class="text-[10px] sm:text-[11px] font-bold px-1.5 sm:px-2 py-0.5 rounded-full flex-shrink-0"
                                          :class="product.stock <= 0 ? 'bg-rose-50 text-rose-600' : (product.stock <= 5 ? 'bg-amber-50 text-amber-700' : 'bg-emerald-50 text-emerald-700')"
                                          x-text="product.stock + ' ' + product.unit"></span>
                                </div>

                                {{-- Product Image Thumbnail / Icon --}}
                                <div class="w-full h-20 sm:h-24 bg-slate-50 rounded-xl mb-2 flex items-center justify-center overflow-hidden border border-slate-100 group-hover:bg-brand-50/30 transition">
                                    <template x-if="product.image_url">
                                        <img :src="product.image_url" :alt="product.name" class="w-full h-full object-cover">
                                    </template>
                                    <template x-if="!product.image_url">
                                        <div class="text-slate-300 group-hover:text-brand-400 transition flex flex-col items-center justify-center">
                                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                            <span class="text-[9px] font-semibold uppercase mt-0.5" x-text="product.unit"></span>
                                        </div>
                                    </template>
                                </div>

                                {{-- Multi-batch Pill Tag --}}
                                <div x-show="product.batches && product.batches.length > 1" class="mb-1">
                                    <span class="inline-flex items-center text-[9px] font-bold px-1.5 py-0.5 rounded-full bg-indigo-50 text-indigo-700 border border-indigo-100">
                                        <svg class="w-2.5 h-2.5 mr-1 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                                        <span x-text="product.batches.length + ' Batches'"></span>
                                    </span>
                                </div>

                                {{-- Product Title --}}
                                <h3 class="text-xs sm:text-sm font-semibold text-slate-800 group-hover:text-brand-600 transition line-clamp-2 leading-snug" x-text="product.name"></h3>
                            </div>

                            {{-- Product Bottom Footer: Price & Add Button --}}
                            <div class="mt-2.5 pt-2 border-t border-slate-100 flex items-center justify-between">
                                <div>
                                    <span class="text-sm sm:text-base font-extrabold text-slate-900" x-text="'₹' + getProductPriceDisplay(product)"></span>
                                    <span class="text-[10px] text-slate-400 block" x-text="'per ' + product.unit"></span>
                                </div>
                                <button type="button"
                                        class="w-7 h-7 sm:w-8 sm:h-8 rounded-xl bg-brand-50 text-brand-700 group-hover:bg-brand-600 group-hover:text-white flex items-center justify-center transition shadow-2xs group-active:scale-90">
                                    <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                                </button>
                            </div>
                        </div>
                    </template>
                </div>

                {{-- Empty Search Result --}}
                <div x-show="filteredProducts.length === 0" class="text-center py-16 text-slate-400">
                    <svg class="w-12 h-12 mx-auto mb-3 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                    <p class="text-sm font-semibold text-slate-700">No products match your search</p>
                    <p class="text-xs mt-1 text-slate-400">Try a different name, SKU, or clear the search query</p>
                </div>
            </div>

            {{-- Floating Mobile Quick Checkout Bar (Only on mobile when cart has items) --}}
            <div x-show="cart.length > 0"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="translate-y-full opacity-0"
                 x-transition:enter-end="translate-y-0 opacity-100"
                 class="lg:hidden fixed bottom-0 inset-x-0 bg-white/95 backdrop-blur-md border-t border-slate-200 px-4 py-3 z-30 shadow-2xl flex items-center justify-between gap-3">
                <div>
                    <span class="text-xs text-slate-500 block"><span class="font-bold text-slate-900" x-text="cartTotalQty"></span> items selected</span>
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

        {{-- Cart & Cashier Section (Full screen on mobile when tab = 'cart', 40% on desktop) --}}
        <div class="w-full lg:w-[460px] xl:w-[500px] flex flex-col bg-white border-t lg:border-t-0 shadow-xl z-10 flex-shrink-0"
             :class="mobileTab === 'cart' ? 'flex' : 'hidden lg:flex'">

            {{-- Mobile Cart Header Navigation --}}
            <div class="lg:hidden px-4 py-2.5 bg-slate-50 border-b border-slate-200 flex items-center justify-between flex-shrink-0">
                <button type="button" @click="mobileTab = 'catalog'" class="text-xs font-bold text-brand-600 flex items-center gap-1 hover:text-brand-800">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    Add More Items
                </button>
                <div class="flex items-center gap-2">
                    <button type="button" x-show="cart.length > 0" @click="holdCurrentCart()" class="text-[11px] font-semibold text-amber-700 hover:text-amber-800 bg-amber-50 px-2 py-0.5 rounded-lg border border-amber-200">
                        Hold Cart
                    </button>
                    <button type="button" x-show="cart.length > 0" @click="clearCartConfirm()" class="text-[11px] font-semibold text-rose-500 hover:text-rose-700">
                        Clear
                    </button>
                </div>
            </div>

            {{-- Customer Selector Card Header --}}
            <div class="p-3 sm:p-3.5 border-b border-slate-200 bg-slate-50/70 space-y-2 flex-shrink-0">
                <div class="flex items-center justify-between gap-2">
                    <div class="flex-1 min-w-0">
                        <label class="block text-[10px] sm:text-[11px] font-semibold uppercase text-slate-500 tracking-wider mb-1">Customer / Orchardist</label>
                        <select x-model="selectedCustomerId" @change="onCustomerChange()"
                                class="w-full text-xs font-medium bg-white border border-slate-200 rounded-xl px-3 py-2 text-slate-800 focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500 shadow-2xs">
                            <option value="">Walk-in Customer (Retail)</option>
                            <template x-for="cust in customers" :key="cust.id">
                                <option :value="cust.id" x-text="cust.name + (cust.phone ? ' (' + cust.phone + ')' : '') + (cust.outstanding_balance > 0 ? ' [Due: ₹' + Number(cust.outstanding_balance).toFixed(0) + ']' : '')"></option>
                            </template>
                        </select>
                    </div>
                    <div class="flex items-center gap-1.5 mt-5">
                        <button type="button" x-show="cart.length > 0" @click="holdCurrentCart()" title="Park current cart to serve next customer"
                                class="hidden sm:inline-flex px-2.5 py-2 text-xs font-semibold text-amber-800 bg-amber-50 hover:bg-amber-100 rounded-xl border border-amber-200 transition items-center gap-1 shadow-2xs">
                            <svg class="w-3.5 h-3.5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Hold
                        </button>
                        <button type="button" @click="showAddCustomer = true"
                                class="px-3 py-2 text-xs font-semibold text-brand-700 bg-brand-50 hover:bg-brand-100 rounded-xl border border-brand-200 transition flex items-center gap-1 flex-shrink-0 shadow-2xs">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            New
                        </button>
                    </div>
                </div>

                {{-- Outstanding Customer Balance Alert Banner --}}
                <div x-show="currentCustomerDue > 0" x-cloak class="bg-amber-50 border border-amber-200 rounded-xl p-2.5 flex items-center justify-between text-xs text-amber-900 shadow-2xs animate-in fade-in">
                    <div class="flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-amber-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        <span>Previous Outstanding Due: <strong class="font-extrabold text-amber-700" x-text="'₹' + currentCustomerDue.toFixed(2)"></strong></span>
                    </div>
                    <button type="button" @click="openSettleBalanceModal()" class="px-2.5 py-1 bg-amber-600 hover:bg-amber-700 active:scale-95 text-white rounded-lg font-bold text-[11px] shadow-xs transition">
                        Settle Balance
                    </button>
                </div>
            </div>

            {{-- Cart Items List Area --}}
            <div class="flex-1 overflow-y-auto p-3 sm:p-3.5 divide-y divide-slate-100 space-y-2">
                <template x-for="(item, idx) in cart" :key="item.cart_key">
                    <div class="pt-2 first:pt-0">
                        <div class="flex items-start justify-between gap-2">
                            <div class="flex-1 min-w-0">
                                <h4 class="text-xs font-semibold text-slate-900 truncate" x-text="item.name"></h4>
                                <div class="flex items-center flex-wrap gap-1.5 mt-1 text-[11px] text-slate-500">
                                    {{-- Editable Unit Rate --}}
                                    <div class="flex items-center gap-1">
                                        <span>Rate: ₹</span>
                                        <input type="number"
                                               x-model.number="item.unit_price"
                                               step="1" min="0"
                                               class="w-16 text-center font-bold text-slate-900 bg-slate-100 border border-slate-200 rounded-lg px-1.5 py-0.5 text-xs focus:bg-white focus:border-brand-500 focus:outline-none">
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
                                <span class="text-xs font-extrabold text-slate-900" x-text="'₹' + itemTotal(item).toFixed(2)"></span>
                                <button type="button" @click="removeFromCart(idx)" class="text-slate-400 hover:text-rose-600 p-1 rounded-lg hover:bg-slate-100 transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </div>
                        </div>

                        {{-- Quantity Stepper --}}
                        <div class="flex items-center justify-between mt-2">
                            <div class="flex items-center border border-slate-200 rounded-xl bg-slate-50 overflow-hidden shadow-2xs">
                                <button type="button" @click="updateQty(item, -1)" class="w-8 h-8 sm:w-7 sm:h-7 flex items-center justify-center text-slate-600 hover:bg-slate-200 active:bg-slate-300 transition font-bold text-base sm:text-sm">-</button>
                                <input type="number"
                                       x-model.number="item.quantity"
                                       @input="if(item.quantity < 0.01) item.quantity = 1"
                                       step="1" min="0.01"
                                       class="w-14 text-center text-xs font-extrabold bg-transparent border-0 focus:ring-0 p-0 text-slate-900">
                                <button type="button" @click="updateQty(item, 1)" class="w-8 h-8 sm:w-7 sm:h-7 flex items-center justify-center text-slate-600 hover:bg-slate-200 active:bg-slate-300 transition font-bold text-base sm:text-sm">+</button>
                            </div>
                            <span class="text-[11px] text-slate-500 font-medium">Line Total: ₹<strong class="text-slate-900" x-text="itemTotal(item).toFixed(0)"></strong></span>
                        </div>
                    </div>
                </template>

                {{-- Empty Cart Placeholder --}}
                <div x-show="cart.length === 0" class="h-full flex flex-col items-center justify-center text-slate-400 py-12">
                    <div class="w-16 h-16 rounded-2xl bg-slate-100 text-slate-300 flex items-center justify-center mb-2">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    </div>
                    <p class="text-xs font-bold text-slate-700">Your cart is empty</p>
                    <p class="text-[11px] text-slate-400 mt-0.5">Click products or scan barcode to add items</p>
                    <button type="button" @click="mobileTab = 'catalog'" class="lg:hidden mt-3 px-3 py-1.5 bg-brand-50 text-brand-700 rounded-lg text-xs font-bold">Browse Products</button>
                </div>
            </div>

            {{-- Cart Calculations & Checkout Footer --}}
            <div class="p-3.5 sm:p-4 bg-slate-50/90 border-t border-slate-200 space-y-2.5 sm:space-y-3 flex-shrink-0 shadow-xs">
                {{-- Pricing Breakdown --}}
                <div class="space-y-1 text-xs text-slate-600">
                    <div class="flex justify-between">
                        <span>Items Subtotal</span>
                        <span class="font-medium text-slate-900" x-text="'₹' + cartSubtotal.toFixed(2)"></span>
                    </div>
                    <div class="flex items-center justify-between gap-2">
                        <span>Special Discount (₹)</span>
                        <input type="number" x-model.number="orderDiscount" min="0" step="1" placeholder="0" class="w-20 text-right text-xs bg-white border border-slate-200 rounded-lg px-2 py-1 font-semibold text-slate-900 focus:outline-none focus:border-brand-500">
                    </div>
                    <div class="flex justify-between" x-show="cartRoundOff !== 0">
                        <span>Round Off</span>
                        <span class="text-slate-500" x-text="(cartRoundOff > 0 ? '+' : '') + cartRoundOff.toFixed(2)"></span>
                    </div>
                    <div class="flex justify-between pt-1.5 border-t border-slate-200 text-sm sm:text-base font-extrabold text-slate-900">
                        <span>Net Payable</span>
                        <span class="text-brand-700 text-base sm:text-lg font-black" x-text="'₹' + cartGrandTotal.toFixed(0)"></span>
                    </div>
                </div>

                {{-- Payment Mode Selector Pills --}}
                <div>
                    <label class="block text-[10px] sm:text-[11px] font-semibold text-slate-500 uppercase tracking-wider mb-1">Payment Tender Mode</label>
                    <div class="grid grid-cols-5 gap-1 text-center">
                        <button type="button" @click="paymentMethod = 'cash'"
                                :class="paymentMethod === 'cash' ? 'bg-emerald-600 text-white font-bold shadow-xs' : 'bg-white text-slate-700 border border-slate-200 hover:bg-slate-50'"
                                class="py-1.5 rounded-xl text-xs transition active:scale-95">Cash</button>
                        <button type="button" @click="paymentMethod = 'upi'"
                                :class="paymentMethod === 'upi' ? 'bg-indigo-600 text-white font-bold shadow-xs' : 'bg-white text-slate-700 border border-slate-200 hover:bg-slate-50'"
                                class="py-1.5 rounded-xl text-xs transition active:scale-95">UPI</button>
                        <button type="button" @click="paymentMethod = 'card'"
                                :class="paymentMethod === 'card' ? 'bg-blue-600 text-white font-bold shadow-xs' : 'bg-white text-slate-700 border border-slate-200 hover:bg-slate-50'"
                                class="py-1.5 rounded-xl text-xs transition active:scale-95">Card</button>
                        <button type="button" @click="paymentMethod = 'bank_transfer'"
                                :class="paymentMethod === 'bank_transfer' ? 'bg-slate-800 text-white font-bold shadow-xs' : 'bg-white text-slate-700 border border-slate-200 hover:bg-slate-50'"
                                class="py-1.5 rounded-xl text-xs transition active:scale-95">Bank</button>
                        <button type="button" @click="paymentMethod = 'split'"
                                :class="paymentMethod === 'split' ? 'bg-purple-600 text-white font-bold shadow-xs' : 'bg-white text-purple-700 border border-purple-200 font-bold hover:bg-purple-50'"
                                class="py-1.5 rounded-xl text-xs transition active:scale-95">Split</button>
                    </div>
                </div>

                {{-- Cash Tender View --}}
                <div x-show="paymentMethod === 'cash'" class="bg-white p-3 rounded-2xl border border-slate-200 space-y-2 shadow-2xs">
                    <div class="flex items-center justify-between gap-2">
                        <span class="text-xs font-semibold text-slate-700">Cash Received:</span>
                        <input type="number" x-model.number="amountTendered" :placeholder="cartGrandTotal" class="w-32 text-right font-extrabold text-sm bg-slate-50 border border-slate-200 rounded-xl px-2.5 py-1 text-slate-900 focus:bg-white focus:border-brand-500 focus:outline-none">
                    </div>

                    {{-- Quick Cash Chips --}}
                    <div class="flex items-center gap-1.5 overflow-x-auto text-[10px] pt-1">
                        <button type="button" @click="amountTendered = cartGrandTotal" class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 active:bg-slate-300 font-semibold text-slate-700 flex-shrink-0 transition">
                            Exact (₹<span x-text="cartGrandTotal"></span>)
                        </button>
                        <button type="button" @click="amountTendered = roundUp(cartGrandTotal, 100)" class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 active:bg-slate-300 font-semibold text-slate-700 flex-shrink-0 transition">
                            ₹<span x-text="roundUp(cartGrandTotal, 100)"></span>
                        </button>
                        <button type="button" @click="amountTendered = roundUp(cartGrandTotal, 500)" class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 active:bg-slate-300 font-semibold text-slate-700 flex-shrink-0 transition">
                            ₹<span x-text="roundUp(cartGrandTotal, 500)"></span>
                        </button>
                        <button type="button" @click="amountTendered = 2000" class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 active:bg-slate-300 font-semibold text-slate-700 flex-shrink-0 transition">
                            ₹2000
                        </button>
                    </div>

                    <div class="text-xs pt-1.5 border-t border-slate-100">
                        <template x-if="cashPaidDue > 0">
                            <div class="flex items-center justify-between text-amber-700 font-semibold">
                                <span>Partial Pay! Balance Due:</span>
                                <span>₹<span x-text="cashPaidDue.toFixed(0)"></span> (Goes to Balance)</span>
                            </div>
                        </template>
                        <template x-if="cashPaidDue <= 0">
                            <div class="flex items-center justify-between text-emerald-600 font-bold">
                                <span class="text-slate-500 font-normal">Change Return:</span>
                                <span>₹<span x-text="changeDue.toFixed(0)"></span></span>
                            </div>
                        </template>
                    </div>
                </div>

                {{-- Digital / Bank Tender View --}}
                <div x-show="['upi', 'card', 'bank_transfer'].includes(paymentMethod)" class="bg-white p-3 rounded-2xl border border-slate-200 space-y-2 shadow-2xs">
                    <div class="flex items-center justify-between gap-2">
                        <span class="text-xs font-semibold text-slate-700">Amount Paid (<span x-text="paymentMethod.toUpperCase()"></span>):</span>
                        <input type="number" x-model.number="digitalAmountPaid" :placeholder="cartGrandTotal" class="w-32 text-right font-extrabold text-sm bg-slate-50 border border-slate-200 rounded-xl px-2.5 py-1 text-slate-900 focus:bg-white focus:border-brand-500 focus:outline-none">
                    </div>
                    <div x-show="digitalPaidDue > 0" class="flex items-center justify-between text-xs text-amber-700 font-semibold pt-1 border-t border-slate-100">
                        <span>Partial Pay! Balance Due:</span>
                        <span>₹<span x-text="digitalPaidDue.toFixed(0)"></span> (Goes to Balance)</span>
                    </div>
                </div>

                {{-- Split Payment Tender View --}}
                <div x-show="paymentMethod === 'split'" class="bg-purple-50/70 p-3 rounded-2xl border border-purple-200 space-y-2 shadow-2xs">
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-xs font-bold text-purple-900">Multi-Tender Breakdown</span>
                        <button type="button" @click="split5050()" class="text-[10px] font-bold text-purple-700 bg-purple-100 hover:bg-purple-200 px-2 py-0.5 rounded-lg transition">
                            Split 50/50 Cash & UPI
                        </button>
                    </div>

                    <div class="grid grid-cols-2 gap-2 text-xs">
                        <div>
                            <label class="block text-[10px] font-semibold text-slate-600 mb-0.5">Cash (₹)</label>
                            <input type="number" x-model.number="splitCash" min="0" placeholder="0" class="w-full text-right font-bold text-xs bg-white border border-slate-200 rounded-xl px-2.5 py-1.5 focus:border-purple-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-[10px] font-semibold text-slate-600 mb-0.5">UPI / QR (₹)</label>
                            <input type="number" x-model.number="splitUpi" min="0" placeholder="0" class="w-full text-right font-bold text-xs bg-white border border-slate-200 rounded-xl px-2.5 py-1.5 focus:border-purple-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-[10px] font-semibold text-slate-600 mb-0.5">Card (₹)</label>
                            <input type="number" x-model.number="splitCard" min="0" placeholder="0" class="w-full text-right font-bold text-xs bg-white border border-slate-200 rounded-xl px-2.5 py-1.5 focus:border-purple-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-[10px] font-semibold text-slate-600 mb-0.5">Bank / Other (₹)</label>
                            <input type="number" x-model.number="splitBank" min="0" placeholder="0" class="w-full text-right font-bold text-xs bg-white border border-slate-200 rounded-xl px-2.5 py-1.5 focus:border-purple-500 focus:outline-none">
                        </div>
                    </div>

                    {{-- Live Split Balance Tracking --}}
                    <div class="pt-2 border-t border-purple-200/80 space-y-1 text-xs">
                        <div class="flex justify-between font-medium text-slate-700">
                            <span>Total Paying Now:</span>
                            <span class="font-bold text-purple-900" x-text="'₹' + splitTotalPaid.toFixed(0)"></span>
                        </div>
                        <template x-if="splitRemainingDue > 0">
                            <div class="flex justify-between font-bold text-amber-800 bg-amber-50 px-2 py-1 rounded-lg border border-amber-200">
                                <span>Remaining Balance Due:</span>
                                <span>₹<span x-text="splitRemainingDue.toFixed(0)"></span> (Recorded in Balance)</span>
                            </div>
                        </template>
                        <template x-if="splitRemainingDue <= 0 && splitChange > 0">
                            <div class="flex justify-between font-bold text-emerald-700">
                                <span>Change Return (Cash):</span>
                                <span>₹<span x-text="splitChange.toFixed(0)"></span></span>
                            </div>
                        </template>
                        <div class="flex items-center gap-1.5 pt-1 text-[10px]">
                            <button type="button" @click="fillRemainingWith('upi')" class="text-purple-700 hover:text-purple-900 underline font-semibold">Fill UPI</button>
                            <span class="text-slate-300">·</span>
                            <button type="button" @click="fillRemainingWith('cash')" class="text-purple-700 hover:text-purple-900 underline font-semibold">Fill Cash</button>
                            <span class="text-slate-300">·</span>
                            <button type="button" @click="clearSplit()" class="text-rose-500 hover:text-rose-700 underline font-semibold">Clear</button>
                        </div>
                    </div>
                </div>

                {{-- Primary Action: Complete Sale Button --}}
                <button type="button"
                        @click="processCheckout()"
                        :disabled="cart.length === 0 || isProcessing"
                        class="w-full py-3.5 px-4 rounded-xl font-bold text-sm text-white bg-brand-600 hover:bg-brand-700 active:scale-[0.99] disabled:opacity-50 disabled:cursor-not-allowed shadow-md hover:shadow-lg transition-all flex items-center justify-center gap-2">
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

    {{-- Batch Selection Modal (Clean & Touch-friendly) --}}
    <div x-show="batchPickerProduct" x-cloak class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-3 sm:p-4 bg-slate-900/60 backdrop-blur-xs">
        <div class="bg-white rounded-2xl max-w-lg w-full p-5 sm:p-6 space-y-4 shadow-2xl animate-in fade-in duration-200" @click.away="batchPickerProduct = null">
            <div class="flex items-start justify-between pb-3 border-b border-slate-100">
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-brand-700">Select Batch & Price</span>
                    <h3 class="text-base font-bold text-slate-900 mt-0.5" x-text="batchPickerProduct?.name"></h3>
                    <p class="text-xs text-slate-500 font-mono" x-text="batchPickerProduct?.sku"></p>
                </div>
                <button type="button" @click="batchPickerProduct = null" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <p class="text-xs text-slate-500">This item has multiple active inventory lots with specific rates. Tap to select batch:</p>

            <div class="space-y-2.5 max-h-72 overflow-y-auto pr-1">
                <template x-for="b in (batchPickerProduct?.batches || [])" :key="b.id">
                    <div @click="addBatchToCart(batchPickerProduct, b)"
                         class="p-3.5 rounded-2xl border border-slate-200 hover:border-brand-500 hover:bg-brand-50/40 transition cursor-pointer flex items-center justify-between gap-3 group active:scale-[0.99]">
                        <div class="space-y-0.5">
                            <div class="flex items-center gap-2">
                                <span class="font-mono font-bold text-xs text-slate-900 group-hover:text-brand-700" x-text="b.batch_number"></span>
                                <span x-show="b.is_near_expiry" class="px-1.5 py-0.2 rounded text-[9px] font-semibold bg-amber-100 text-amber-800">Near Expiry</span>
                            </div>
                            <div class="text-[11px] text-slate-500 flex items-center gap-3">
                                <span>Avail: <strong class="text-emerald-700 font-bold" x-text="b.current_qty + ' ' + batchPickerProduct.unit"></strong></span>
                                <span x-show="b.expiry">Exp: <span x-text="b.expiry"></span></span>
                            </div>
                        </div>

                        <div class="flex items-center gap-3">
                            <div class="text-right">
                                <span class="text-base font-black text-slate-900 block" x-text="'₹' + Number(b.selling_price).toFixed(0)"></span>
                                <span class="text-[10px] text-slate-400">per <span x-text="batchPickerProduct.unit"></span></span>
                            </div>
                            <button type="button"
                                    class="px-3.5 py-1.5 bg-brand-600 group-hover:bg-brand-700 text-white rounded-xl text-xs font-bold transition shadow-xs">
                                Select
                            </button>
                        </div>
                    </div>
                </template>
            </div>

            <div class="flex justify-end pt-2 border-t border-slate-100">
                <button type="button" @click="batchPickerProduct = null" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl transition">
                    Cancel
                </button>
            </div>
        </div>
    </div>

    {{-- Held Carts Modal --}}
    <div x-show="showHeldCarts" x-cloak class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-3 sm:p-4 bg-slate-900/60 backdrop-blur-xs">
        <div class="bg-white rounded-2xl max-w-md w-full p-5 sm:p-6 space-y-4 shadow-2xl" @click.away="showHeldCarts = false">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div>
                    <h3 class="text-base font-bold text-slate-900">Held (Parked) Carts</h3>
                    <p class="text-xs text-slate-500">Resume any customer sale on hold</p>
                </div>
                <button type="button" @click="showHeldCarts = false" class="text-slate-400 hover:text-slate-600 p-1">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="space-y-2.5 max-h-80 overflow-y-auto pr-1">
                <template x-for="(hc, idx) in heldCarts" :key="hc.id">
                    <div class="p-3.5 rounded-2xl border border-slate-200 bg-slate-50 flex items-center justify-between gap-3">
                        <div>
                            <p class="text-xs font-bold text-slate-900" x-text="hc.customer_name"></p>
                            <p class="text-[11px] text-slate-500" x-text="hc.items.length + ' items · ₹' + hc.total.toFixed(0) + ' · ' + hc.time"></p>
                        </div>
                        <div class="flex items-center gap-2">
                            <button type="button" @click="resumeHeldCart(idx)" class="px-3.5 py-1.5 bg-brand-600 hover:bg-brand-700 text-white rounded-xl text-xs font-bold shadow-xs">
                                Resume
                            </button>
                            <button type="button" @click="deleteHeldCart(idx)" class="p-1.5 text-slate-400 hover:text-rose-600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>

    {{-- Settle Balance Modal --}}
    <div x-show="showSettleBalanceModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-3 sm:p-4 bg-slate-900/60 backdrop-blur-xs">
        <div class="bg-white rounded-2xl max-w-sm sm:max-w-md w-full p-5 sm:p-6 space-y-4 shadow-2xl" @click.away="showSettleBalanceModal = false">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h3 class="text-base font-bold text-slate-900">Settle Customer Balance</h3>
                <button type="button" @click="showSettleBalanceModal = false" class="text-slate-400 hover:text-slate-600 p-1">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="bg-amber-50 p-3.5 rounded-2xl border border-amber-200 text-xs text-amber-900">
                <p>Customer: <strong class="font-bold" x-text="selectedCustomer?.name"></strong></p>
                <p class="mt-1">Current Outstanding Due: <strong class="text-base text-amber-700" x-text="'₹' + currentCustomerDue.toFixed(2)"></strong></p>
            </div>

            <form @submit.prevent="submitSettleBalance()" class="space-y-3.5 text-xs">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Payment Amount (₹) <span class="text-rose-500">*</span></label>
                    <input type="number" x-model.number="settleAmount" :max="currentCustomerDue" step="1" required class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm font-extrabold focus:border-brand-500 focus:outline-none">
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Payment Mode <span class="text-rose-500">*</span></label>
                    <select x-model="settlePaymentMethod" required class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none">
                        <option value="cash">Cash</option>
                        <option value="upi">UPI / Online QR</option>
                        <option value="card">Card</option>
                        <option value="bank_transfer">Bank Transfer</option>
                    </select>
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Receipt / Remarks Note</label>
                    <input type="text" x-model="settleNote" placeholder="e.g. Settle previous bill balance" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none">
                </div>

                <div class="flex justify-end gap-2 pt-3">
                    <button type="button" @click="showSettleBalanceModal = false" class="px-4 py-2 rounded-xl text-slate-600 hover:bg-slate-100 font-semibold">Cancel</button>
                    <button type="submit" class="px-4 py-2 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-bold shadow-xs">Record Payment</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Sale Completed Modal --}}
    <div x-show="completedSale" x-cloak class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-3 sm:p-4 bg-slate-900/60 backdrop-blur-xs">
        <div class="bg-white rounded-3xl max-w-sm sm:max-w-md w-full p-5 sm:p-6 text-center space-y-4 shadow-2xl animate-in fade-in zoom-in duration-200">
            <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-2xl bg-emerald-100 text-emerald-600 flex items-center justify-center mx-auto shadow-sm">
                <svg class="w-7 h-7 sm:w-8 sm:h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
            </div>
            <div>
                <h3 class="text-lg sm:text-xl font-extrabold text-slate-900">Sale Completed!</h3>
                <p class="text-xs sm:text-sm text-slate-500 mt-1">Invoice: <strong class="text-slate-900 font-mono" x-text="completedSale?.invoice_number"></strong></p>
                <div class="mt-2.5 py-2.5 px-3 bg-slate-50 rounded-2xl space-y-1 border border-slate-100">
                    <p class="text-sm font-semibold text-slate-600">Total Bill: <strong class="text-slate-900 font-extrabold" x-text="'₹' + completedSale?.grand_total"></strong></p>
                    <p class="text-xs text-emerald-600 font-bold">Amount Paid: <strong x-text="'₹' + completedSale?.amount_paid"></strong></p>
                    <template x-if="completedSale?.balance_due > 0">
                        <p class="text-xs text-amber-800 font-bold bg-amber-100 px-2.5 py-1 rounded-xl">Added to Customer Due: ₹<span x-text="completedSale?.balance_due"></span></p>
                    </template>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-2.5 sm:gap-3 pt-2">
                <a :href="completedSale?.receipt_url" target="_blank" class="py-2.5 px-3 bg-slate-100 hover:bg-slate-200 active:bg-slate-300 text-slate-800 rounded-xl font-bold text-xs transition flex items-center justify-center gap-1.5 shadow-2xs">
                    <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    80mm Receipt
                </a>
                <a :href="completedSale?.invoice_url" target="_blank" class="py-2.5 px-3 bg-brand-50 hover:bg-brand-100 active:bg-brand-200 text-brand-700 rounded-xl font-bold text-xs transition flex items-center justify-center gap-1.5 shadow-2xs">
                    <svg class="w-4 h-4 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    A4 Invoice
                </a>
            </div>

            <button type="button" @click="resetCart()" class="w-full py-3.5 bg-brand-600 hover:bg-brand-700 active:scale-[0.99] text-white rounded-2xl font-bold text-sm transition shadow-md">
                Next Customer / New Sale
            </button>
        </div>
    </div>

    {{-- Add POS Customer Modal --}}
    <div x-show="showAddCustomer" x-cloak class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-3 sm:p-4 bg-slate-900/60 backdrop-blur-xs">
        <div class="bg-white rounded-3xl max-w-sm sm:max-w-md w-full p-5 sm:p-6 space-y-4 shadow-2xl" @click.away="showAddCustomer = false">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h3 class="text-base font-bold text-slate-900">Add POS Customer / Orchardist</h3>
                <button type="button" @click="showAddCustomer = false" class="text-slate-400 hover:text-slate-600 p-1">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form @submit.prevent="saveCustomer()" class="space-y-3.5 text-xs">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Customer / Farmer Name <span class="text-rose-500">*</span></label>
                    <input type="text" x-model="newCustomer.name" required class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500">
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Phone Number</label>
                    <input type="text" x-model="newCustomer.phone" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500">
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Address / Village</label>
                    <textarea x-model="newCustomer.address" rows="2" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500"></textarea>
                </div>

                <div class="flex justify-end gap-2 pt-3">
                    <button type="button" @click="showAddCustomer = false" class="px-4 py-2 rounded-xl text-slate-600 hover:bg-slate-100 font-semibold">Cancel</button>
                    <button type="submit" class="px-4 py-2 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-bold shadow-xs">Save & Select</button>
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
                'image_url' => \App\Support\Media::url($p->image),
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
            paymentMethod: 'cash',
            amountTendered: null,
            digitalAmountPaid: null,
            // Split amounts
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
                if (product.batches && product.batches.length > 1) {
                    this.batchPickerProduct = product;
                } else if (product.batches && product.batches.length === 1) {
                    this.addBatchToCart(product, product.batches[0]);
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
                // Customer selection changed
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

                // If balance is due, ensure a named customer is selected
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
