@extends('admin.layout')

@section('page-title', 'Notification Templates')

@section('content')
<div class="space-y-6" x-data="{ activeTab: '{{ $activeTab }}' }">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-3">
                <h2 class="text-2xl font-bold text-gray-900">Notification Templates</h2>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 mr-1.5 animate-pulse"></span>
                    Firebase & System Push
                </span>
            </div>
            <p class="text-sm text-gray-500 mt-1">
                Customize titles, messages, and tags for automatic Firebase push alerts, mobile app notifications, and emails.
            </p>
        </div>

        <div class="flex items-center gap-3">
            <x-admin.button href="{{ route('admin.mobile-apps.index', ['tab' => 'push']) }}" variant="secondary" icon='<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>'>
                Push Dashboard
            </x-admin.button>
            <x-admin.button href="{{ route('admin.automation.index') }}" variant="secondary" icon='<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-1.066 2.573c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>'>
                Automation Rules
            </x-admin.button>
        </div>
    </div>

    {{-- Tabs --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 px-2 py-1">
        <nav class="flex gap-1 overflow-x-auto" aria-label="Notification templates tabs">
            <button @click="activeTab = 'stages'"
                :class="activeTab === 'stages' ? 'bg-brand-50 text-brand-700 border-brand-200' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50 border-transparent'"
                class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-semibold rounded-xl border transition-all whitespace-nowrap">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                Service Stage Templates
                <span class="px-1.5 py-0.5 text-xs font-bold rounded-full bg-emerald-100 text-emerald-800">{{ $services->sum(fn($s) => $s->stages->count()) }}</span>
            </button>
            <button @click="activeTab = 'system'"
                :class="activeTab === 'system' ? 'bg-brand-50 text-brand-700 border-brand-200' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50 border-transparent'"
                class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-semibold rounded-xl border transition-all whitespace-nowrap">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                System & Event Notifications
                <span class="px-1.5 py-0.5 text-xs font-bold rounded-full bg-blue-100 text-blue-800">{{ count($systemTemplates) }}</span>
            </button>
        </nav>
    </div>

    {{-- TAB 1: SERVICE STAGE TEMPLATES --}}
    <div x-show="activeTab === 'stages'" x-cloak class="space-y-6">
        <div class="bg-emerald-50/70 border border-emerald-200/80 rounded-2xl p-4 flex items-start gap-3">
            <div class="p-2 bg-emerald-100 text-emerald-700 rounded-xl flex-shrink-0 mt-0.5">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div class="text-xs text-emerald-900 leading-relaxed">
                <p class="font-bold text-sm text-emerald-950">How Stage Notifications Work</p>
                <p class="mt-0.5">
                    When a field agent completes a stage on an active Work Order, the farmer receives an instant Firebase push notification along with the uploaded field photos. You can enable/disable push notifications and customize the exact title and message for each stage below.
                </p>
                <p class="mt-1 font-medium text-emerald-800">
                    Supported tags: <code class="bg-white/80 px-1.5 py-0.5 rounded text-emerald-900 font-mono">{stage_name}</code>, <code class="bg-white/80 px-1.5 py-0.5 rounded text-emerald-900 font-mono">{work_order_number}</code>, <code class="bg-white/80 px-1.5 py-0.5 rounded text-emerald-900 font-mono">{service_name}</code>, <code class="bg-white/80 px-1.5 py-0.5 rounded text-emerald-900 font-mono">{orchard_name}</code>, <code class="bg-white/80 px-1.5 py-0.5 rounded text-emerald-900 font-mono">{customer_name}</code>.
                </p>
            </div>
        </div>

        @forelse($services as $service)
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden" x-data="{ expanded: true }">
                <div class="p-4 bg-gray-50/80 border-b border-gray-100 flex items-center justify-between cursor-pointer select-none" @click="expanded = !expanded">
                    <div class="flex items-center gap-3">
                        <span class="w-8 h-8 rounded-lg bg-brand-600 text-white font-bold flex items-center justify-center text-xs shadow-sm">
                            {{ substr($service->name, 0, 1) }}
                        </span>
                        <div>
                            <h3 class="font-bold text-gray-900 text-base">{{ $service->name }}</h3>
                            <p class="text-xs text-gray-500">{{ $service->stages->count() }} stages in workflow</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <a href="{{ route('admin.services.stages.index', $service) }}" @click.stop class="text-xs font-semibold text-brand-600 hover:text-brand-800 bg-white border border-gray-200 px-3 py-1.5 rounded-lg shadow-2xs transition">
                            Manage Stages Kanban &rarr;
                        </a>
                        <svg class="w-5 h-5 text-gray-400 transition-transform duration-200" :class="expanded ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                </div>

                <div x-show="expanded" x-cloak x-collapse class="divide-y divide-gray-100">
                    @forelse($service->stages as $index => $stage)
                        <form action="{{ route('admin.notification-templates.stage.update', $stage) }}" method="POST" class="p-5 hover:bg-gray-50/40 transition">
                            @csrf
                            @method('PUT')
                            <div class="flex flex-col lg:flex-row lg:items-start justify-between gap-5">
                                {{-- Stage info & toggle --}}
                                <div class="lg:w-1/4 space-y-2">
                                    <div class="flex items-center gap-2">
                                        <span class="w-6 h-6 rounded bg-gray-100 text-gray-700 font-extrabold text-xs flex items-center justify-center">
                                            {{ $index + 1 }}
                                        </span>
                                        <h4 class="font-bold text-gray-900 text-sm">{{ $stage->name }}</h4>
                                    </div>
                                    @if($stage->description)
                                        <p class="text-xs text-gray-500 line-clamp-2">{{ $stage->description }}</p>
                                    @endif

                                    <div class="pt-2">
                                        <label class="flex items-center gap-2 cursor-pointer select-none">
                                            <input type="checkbox" name="notify_customer" value="1" {{ ($stage->notify_customer ?? true) ? 'checked' : '' }} class="rounded border-gray-300 text-brand-600 focus:ring-brand-500 w-4 h-4">
                                            <span class="text-xs font-semibold text-gray-700">Send Firebase Push</span>
                                        </label>
                                    </div>
                                </div>

                                {{-- Template Inputs --}}
                                <div class="lg:w-3/4 grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 mb-1">
                                            Notification Title
                                            <span class="text-gray-400 font-normal">(Leave blank for default)</span>
                                        </label>
                                        <input type="text" name="notification_title" value="{{ old('notification_title', $stage->notification_title) }}" placeholder="Stage Completed: {stage_name}" class="w-full text-xs rounded-xl border-gray-200 focus:border-brand-500 focus:ring-brand-500 py-2">
                                    </div>

                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 mb-1">
                                            Notification Message Body
                                            <span class="text-gray-400 font-normal">(Leave blank for default)</span>
                                        </label>
                                        <textarea name="notification_body" rows="2" placeholder="Stage '{stage_name}' for work order #{work_order_number} has been completed successfully." class="w-full text-xs rounded-xl border-gray-200 focus:border-brand-500 focus:ring-brand-500 py-2">{{ old('notification_body', $stage->notification_body) }}</textarea>
                                    </div>

                                    <div class="md:col-span-2 flex items-center justify-between pt-1">
                                        <div class="text-[11px] text-gray-400">
                                            Defaults: <span class="text-gray-600 font-mono">Stage Completed: {{ $stage->name }}</span>
                                        </div>
                                        <x-admin.button type="submit" size="sm">Save Stage Template</x-admin.button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    @empty
                        <div class="p-6 text-center text-xs text-gray-400">
                            No stages defined for this service.
                        </div>
                    @endforelse
                </div>
            </div>
        @empty
            <div class="bg-white rounded-2xl p-12 text-center text-gray-500 border border-gray-100">
                <p>No services registered yet.</p>
            </div>
        @endforelse
    </div>

    {{-- TAB 2: SYSTEM EVENT NOTIFICATIONS --}}
    <div x-show="activeTab === 'system'" x-cloak class="space-y-6">
        <form action="{{ route('admin.notification-templates.system.update') }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="flex items-center justify-between bg-white rounded-2xl shadow-sm border border-gray-100 p-4">
                <div>
                    <h3 class="font-bold text-gray-900 text-sm">System Event Notification Templates</h3>
                    <p class="text-xs text-gray-500">Edit push notification titles, messages, and tags for lifecycle and operations events.</p>
                </div>
                <x-admin.button type="submit">Save All System Templates</x-admin.button>
            </div>

            @php
                $grouped = collect($systemTemplates)->groupBy('category');
            @endphp

            @foreach($grouped as $category => $templates)
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="p-4 bg-gray-50/70 border-b border-gray-100 flex items-center justify-between">
                        <h3 class="font-bold text-gray-900 text-sm flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-brand-500"></span>
                            {{ $category }}
                        </h3>
                        <span class="text-xs text-gray-500">{{ count($templates) }} template(s)</span>
                    </div>

                    <div class="divide-y divide-gray-100">
                        @foreach($templates as $key => $template)
                            <div class="p-5 space-y-4 hover:bg-gray-50/30 transition">
                                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <h4 class="font-bold text-gray-900 text-sm">{{ $template['name'] }}</h4>
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-gray-100 text-gray-600">
                                                {{ $template['recipient'] }}
                                            </span>
                                        </div>
                                        <p class="text-xs text-gray-500 mt-0.5">{{ $template['description'] }}</p>
                                    </div>

                                    <div class="flex items-center gap-2">
                                        <label class="flex items-center gap-2 cursor-pointer select-none">
                                            <input type="checkbox" name="templates[{{ $key }}][fcm_enabled]" value="1" {{ !empty($template['fcm_enabled']) ? 'checked' : '' }} class="rounded border-gray-300 text-brand-600 focus:ring-brand-500 w-4 h-4">
                                            <span class="text-xs font-semibold text-gray-700">FCM Push Enabled</span>
                                        </label>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 mb-1">Push & Notification Title</label>
                                        <input type="text" name="templates[{{ $key }}][title]" value="{{ old('templates.'.$key.'.title', $template['title']) }}" required class="w-full text-xs rounded-xl border-gray-200 focus:border-brand-500 focus:ring-brand-500 py-2">
                                    </div>

                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 mb-1">Notification Message Body</label>
                                        <textarea name="templates[{{ $key }}][body]" rows="2" required class="w-full text-xs rounded-xl border-gray-200 focus:border-brand-500 focus:ring-brand-500 py-2">{{ old('templates.'.$key.'.body', $template['body']) }}</textarea>
                                    </div>
                                </div>

                                {{-- Available tags --}}
                                @if(!empty($template['available_tags']))
                                    <div class="flex items-center gap-1.5 flex-wrap text-xs pt-1">
                                        <span class="text-gray-400 text-[11px] font-medium mr-1">Dynamic Placeholders:</span>
                                        @foreach($template['available_tags'] as $tag)
                                            <span class="px-2 py-0.5 rounded-md bg-gray-100 hover:bg-emerald-50 hover:text-emerald-800 text-gray-700 font-mono text-[11px] transition select-all" title="Click to copy tag">
                                                {{ $tag }}
                                            </span>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach

            <div class="flex justify-end">
                <x-admin.button type="submit">Save All System Templates</x-admin.button>
            </div>
        </form>
    </div>
</div>
@endsection
