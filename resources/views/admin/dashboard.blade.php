@extends('admin.layout')

@section('page-title', 'Dashboard')

@section('content')
    <div class="space-y-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Welcome back, {{ Auth::guard('admin')->user()->name }}!</h2>
            <p class="text-gray-500 mt-1">Here's an overview of your admin panel.</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-sm font-medium text-gray-500">Total Leads</span>
                    <div class="w-9 h-9 bg-brand-50 text-brand-600 rounded-xl flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                </div>
                <p class="text-3xl font-bold text-gray-900">{{ \App\Models\Lead::count() }}</p>
                <p class="text-xs text-gray-400 mt-1">{{ \App\Models\Lead::where('status', 'new')->count() }} awaiting first call</p>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-sm font-medium text-gray-500">Customers</span>
                    <div class="w-9 h-9 bg-brand-50 text-brand-600 rounded-xl flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </div>
                </div>
                <p class="text-3xl font-bold text-gray-900">{{ \App\Models\Customer::count() }}</p>
                <p class="text-xs text-gray-400 mt-1">{{ \App\Models\Customer::where('status', 'active')->count() }} active</p>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-sm font-medium text-gray-500">Services</span>
                    <div class="w-9 h-9 bg-brand-50 text-brand-600 rounded-xl flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                    </div>
                </div>
                <p class="text-3xl font-bold text-gray-900">{{ \App\Models\Service::count() }}</p>
                <p class="text-xs text-gray-400 mt-1">{{ \App\Models\Service::where('is_active', true)->count() }} active on the site</p>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-sm font-medium text-gray-500">Theme</span>
                    <div class="w-9 h-9 bg-brand-50 text-brand-600 rounded-xl flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/>
                        </svg>
                    </div>
                </div>
                <p class="text-3xl font-bold text-gray-900 capitalize">@php $p = config('theme.palettes')[config('shop.theme_palette', 'emerald')] ?? null; echo $p['label'] ?? 'Emerald'; @endphp</p>
                <p class="text-xs text-gray-400 mt-1">Active color palette</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-1">Quick Actions</h3>
                <p class="text-sm text-gray-500 mb-4">Jump to the most used sections.</p>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <a href="{{ route('admin.frontend.index', ['tab' => 'lead_form']) }}"
                       class="flex items-center justify-between px-4 py-3 bg-gray-50 border border-gray-100 rounded-xl text-sm font-medium text-gray-700 hover:bg-brand-50 hover:border-brand-200 hover:text-brand-700 transition">
                        Lead Form
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                    <a href="{{ route('admin.leads.index') }}"
                       class="flex items-center justify-between px-4 py-3 bg-gray-50 border border-gray-100 rounded-xl text-sm font-medium text-gray-700 hover:bg-brand-50 hover:border-brand-200 hover:text-brand-700 transition">
                        Leads
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                    <a href="{{ route('admin.services.index') }}"
                       class="flex items-center justify-between px-4 py-3 bg-gray-50 border border-gray-100 rounded-xl text-sm font-medium text-gray-700 hover:bg-brand-50 hover:border-brand-200 hover:text-brand-700 transition">
                        Services
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                    <a href="{{ route('admin.settings.index', ['tab' => 'appearance']) }}"
                       class="flex items-center justify-between px-4 py-3 bg-gray-50 border border-gray-100 rounded-xl text-sm font-medium text-gray-700 hover:bg-brand-50 hover:border-brand-200 hover:text-brand-700 transition">
                        Appearance
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-1">Contact Information</h3>
                <p class="text-sm text-gray-500 mb-4">Currently configured contact details.</p>
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between items-center py-2 border-b border-gray-50">
                        <span class="text-gray-500">Email</span>
                        <span class="font-medium text-gray-800">{{ config('shop.site_email', '-') }}</span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b border-gray-50">
                        <span class="text-gray-500">Phone</span>
                        <span class="font-medium text-gray-800">{{ config('shop.site_phone', '-') }}</span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b border-gray-50">
                        <span class="text-gray-500">Support Hours</span>
                        <span class="font-medium text-gray-800">{{ config('shop.support_hours', '-') }}</span>
                    </div>
                    <div class="flex justify-between items-center py-2">
                        <span class="text-gray-500">Site Name</span>
                        <span class="font-medium text-gray-800">{{ config('shop.site_name', '-') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
