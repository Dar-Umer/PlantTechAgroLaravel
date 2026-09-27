@extends('admin.layout')

@section('page-title', 'Services & Work Stages')

@section('content')
    <div class="space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Services &amp; Work Stages</h2>
                <p class="text-sm text-gray-500 mt-0.5">Manage agricultural services, pricing units, quotation density variations, invoice defaults, and multi-stage workflows.</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.settings.index', ['tab' => 'invoice']) }}" class="px-3.5 py-2 rounded-xl border border-gray-200 bg-white hover:bg-gray-50 text-xs font-semibold text-gray-700 shadow-2xs transition inline-flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <span>Global Invoices &amp; Quotations Settings</span>
                </a>
                <x-admin.button href="{{ route('admin.services.create') }}" variant="primary" icon='<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>'>
                    Create Service
                </x-admin.button>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="px-6 py-3 font-semibold text-gray-600">Service Name</th>
                            <th class="px-6 py-3 font-semibold text-gray-600">Pricing &amp; Variations</th>
                            <th class="px-6 py-3 font-semibold text-gray-600">Category</th>
                            <th class="px-6 py-3 font-semibold text-gray-600">Workflow Stages</th>
                            <th class="px-6 py-3 font-semibold text-gray-600">Status</th>
                            <th class="px-6 py-3 font-semibold text-gray-600 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($services as $service)
                            @php
                                $sDefs = $service->getQuotationDefaults();
                                $varCount = count($sDefs['variations'] ?? []);
                                $unit = $sDefs['unit'] ?? 'Kanal';
                                $basePrice = $sDefs['base_price'] ?? null;
                            @endphp
                            <tr class="hover:bg-gray-50/70 transition">
                                <td class="px-6 py-4">
                                    <div class="font-bold text-gray-900 text-sm flex items-center gap-2">
                                        <a href="{{ route('admin.services.edit', $service) }}" class="hover:text-brand-600 transition">
                                            {{ $service->name }}
                                        </a>
                                        @if($service->creates_orchard_on_completion)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">Orchard Builder</span>
                                        @endif
                                    </div>
                                    @if($service->description)
                                        <div class="text-xs text-gray-400 mt-0.5 line-clamp-1">{{ $service->description }}</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <div class="space-y-1">
                                        <div class="text-xs font-bold text-gray-900">
                                            @if(! $service->requiresUnit())
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-semibold bg-indigo-50 text-indigo-700 border border-indigo-200">No Unit (Call/Advisory)</span>
                                            @elseif($basePrice)
                                                ₹{{ number_format($basePrice, 2) }} <span class="text-gray-500 font-medium">/ {{ $unit }}</span>
                                            @else
                                                <span class="text-gray-400 font-normal">Custom Rate / {{ $unit }}</span>
                                            @endif
                                        </div>
                                        <a href="{{ route('admin.services.edit', [$service, 'tab' => 'quotation']) }}" class="inline-flex items-center gap-1 text-[11px] font-semibold text-brand-700 hover:text-brand-900 transition">
                                            @if($varCount > 0)
                                                <span class="px-2 py-0.5 rounded-full bg-brand-50 border border-brand-200 text-brand-700">{{ $varCount }} variation{{ $varCount > 1 ? 's' : '' }}</span>
                                            @else
                                                <span class="text-gray-400 hover:underline">+ Add variations</span>
                                            @endif
                                        </a>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-gray-600">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-brand-50 text-brand-700">
                                        {{ $service->category }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <a href="{{ route('admin.services.stages.index', $service) }}"
                                       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-emerald-50 text-emerald-700 hover:bg-emerald-100 font-bold text-xs border border-emerald-200 transition shadow-2xs group"
                                       title="Open Drag & Drop Kanban Workflow">
                                        <svg class="w-3.5 h-3.5 text-emerald-600 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/></svg>
                                        <span>Drag Stages ({{ $service->stages_count ?? $service->stages()->count() }})</span>
                                        <span class="text-[10px] text-emerald-500 font-normal">&rarr;</span>
                                    </a>
                                </td>
                                <td class="px-6 py-4">
                                    @if($service->is_active)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-green-50 text-green-700 border border-green-200">Active</span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-50 text-red-700 border border-red-200">Inactive</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <a href="{{ route('admin.services.edit', [$service, 'tab' => 'quotation']) }}"
                                           class="px-2.5 py-1 rounded-lg border border-gray-200 bg-white hover:bg-gray-50 text-gray-700 font-semibold text-xs transition shadow-2xs"
                                           title="Edit Pricing & Variations">
                                            Pricing
                                        </a>
                                        <a href="{{ route('admin.services.stages.index', $service) }}"
                                           class="px-2.5 py-1 rounded-lg bg-emerald-600 text-white hover:bg-emerald-700 font-semibold text-xs transition shadow-2xs flex items-center gap-1"
                                           title="Kanban Stages">
                                            <span>Stages</span>
                                        </a>
                                        <x-admin.button href="{{ route('admin.services.edit', $service) }}" variant="secondary" size="sm">
                                            Edit
                                        </x-admin.button>
                                        <form action="{{ route('admin.services.destroy', $service) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this service?')">
                                            @csrf
                                            @method('DELETE')
                                            <x-admin.button type="submit" variant="danger" size="sm">Delete</x-admin.button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                    <div class="flex flex-col items-center">
                                        <svg class="w-12 h-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                        </svg>
                                        <p class="text-sm">No services found.</p>
                                        <a href="{{ route('admin.services.create') }}" class="mt-2 text-sm text-brand-600 hover:text-brand-700 font-medium">Create your first service</a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($services->hasPages())
                <div class="px-6 py-4 border-t border-gray-100">
                    {{ $services->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
