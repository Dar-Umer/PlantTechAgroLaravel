@extends('admin.layout')

@section('page-title', 'Orchards')

@section('content')
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Orchards</h2>
                <p class="text-sm text-gray-500 mt-1">Manage farm holdings, tracking company-established orchards and customer-registered farms.</p>
            </div>
            <x-admin.button href="{{ route('admin.orchards.create') }}" variant="primary" icon='<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>'>
                Register Orchard
            </x-admin.button>
        </div>

        {{-- Stat Cards matching Work Orders & Products index vibe --}}
        <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-5 gap-4">
            <a href="{{ route('admin.orchards.index') }}"
               class="group bg-white rounded-2xl shadow-xs hover:shadow-md border p-5 transition-all duration-200 hover:-translate-y-0.5 {{ !request('type') ? 'border-brand-300 ring-2 ring-brand-100' : 'border-gray-100 hover:border-brand-200' }}">
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">All Orchards</p>
                <p class="text-3xl font-bold text-gray-900 mt-2">{{ $stats['total'] }}</p>
                <p class="text-xs text-gray-400 mt-1">Total registered</p>
            </a>
            <a href="{{ route('admin.orchards.index', ['type' => 'company']) }}"
               class="group bg-white rounded-2xl shadow-xs hover:shadow-md border p-5 transition-all duration-200 hover:-translate-y-0.5 {{ request('type') === 'company' ? 'border-brand-300 ring-2 ring-brand-100' : 'border-gray-100 hover:border-brand-200' }}">
                <p class="text-xs font-semibold uppercase tracking-wider text-emerald-700">By Plant Tech Agro</p>
                <p class="text-3xl font-bold text-emerald-800 mt-2">{{ $stats['company'] }}</p>
                <p class="text-xs text-emerald-600 mt-1">Company established</p>
            </a>
            <a href="{{ route('admin.orchards.index', ['type' => 'self']) }}"
               class="group bg-white rounded-2xl shadow-xs hover:shadow-md border p-5 transition-all duration-200 hover:-translate-y-0.5 {{ request('type') === 'self' ? 'border-brand-300 ring-2 ring-brand-100' : 'border-gray-100 hover:border-brand-200' }}">
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Self Registered</p>
                <p class="text-3xl font-bold text-gray-900 mt-2">{{ $stats['self'] }}</p>
                <p class="text-xs text-gray-400 mt-1">Added by farmers</p>
            </a>
            <div class="bg-white rounded-2xl shadow-xs border border-gray-100 p-5">
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Total Cultivated Area</p>
                <p class="text-3xl font-bold text-gray-900 mt-2">{{ number_format($stats['total_kanals'], 1) }} <span class="text-sm font-normal text-gray-500">K</span></p>
                <p class="text-xs text-gray-400 mt-1">Kanals under management</p>
            </div>
            <div class="bg-white rounded-2xl shadow-xs border border-gray-100 p-5">
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Total Plants</p>
                <p class="text-3xl font-bold text-gray-900 mt-2">{{ number_format($stats['total_plants']) }}</p>
                <p class="text-xs text-gray-400 mt-1">Trees & rootstocks</p>
            </div>
        </div>

        {{-- Filter & Search Bar matching Customers/Work Orders --}}
        <form method="GET" action="{{ route('admin.orchards.index') }}" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 flex flex-wrap items-center gap-3">
            <div class="flex-1 min-w-[200px]">
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Search by orchard name, Orchard ID, farmer, or address..."
                       class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
            </div>
            <div>
                <select name="type" class="rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                    <option value="">All Types</option>
                    <option value="company" {{ request('type') === 'company' ? 'selected' : '' }}>Established by PTA</option>
                    <option value="self" {{ request('type') === 'self' ? 'selected' : '' }}>Self Registered</option>
                </select>
            </div>
            <x-admin.button type="submit" variant="primary">Filter</x-admin.button>
            @if(request('q') || request('type'))
                <a href="{{ route('admin.orchards.index') }}" class="text-sm text-gray-500 hover:text-gray-700">Clear</a>
            @endif
        </form>

        {{-- Data Table matching standard table style --}}
        <div class="bg-white rounded-2xl shadow-xs border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-gray-50/80 border-b border-gray-100">
                        <tr>
                            <th class="px-6 py-3.5 text-xs font-semibold uppercase tracking-wider text-gray-500">Orchard</th>
                            <th class="px-6 py-3.5 text-xs font-semibold uppercase tracking-wider text-gray-500">Orchardist (Customer)</th>
                            <th class="px-6 py-3.5 text-xs font-semibold uppercase tracking-wider text-gray-500">Area & Plants</th>
                            <th class="px-6 py-3.5 text-xs font-semibold uppercase tracking-wider text-gray-500">Age</th>
                            <th class="px-6 py-3.5 text-xs font-semibold uppercase tracking-wider text-gray-500">Provenance</th>
                            <th class="px-6 py-3.5 text-xs font-semibold uppercase tracking-wider text-gray-500 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($orchards as $orchard)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4">
                                    <div>
                                        <a href="{{ route('admin.orchards.show', $orchard) }}" class="font-semibold text-brand-600 hover:text-brand-700">
                                            {{ $orchard->name }}
                                        </a>
                                        <div class="flex items-center gap-2 mt-0.5">
                                            <span class="font-mono text-xs font-medium text-gray-600 bg-gray-100 px-1.5 py-0.5 rounded">
                                                {{ $orchard->orchard_id }}
                                            </span>
                                            @if($orchard->address)
                                                <span class="text-xs text-gray-500 truncate max-w-[200px]" title="{{ $orchard->address }}">
                                                    {{ $orchard->address }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    @if($orchard->customer)
                                        <a href="{{ route('admin.customers.show', $orchard->customer) }}" class="font-medium text-gray-900 hover:text-brand-600">
                                            {{ $orchard->customer->name }}
                                        </a>
                                        <div class="flex items-center gap-2 mt-0.5">
                                            <span class="font-mono text-[11px] font-semibold text-indigo-700 bg-indigo-50 px-1.5 py-0.2 rounded border border-indigo-100">
                                                {{ $orchard->customer->orchardist_id ?? 'OID-N/A' }}
                                            </span>
                                            <span class="text-xs text-gray-500 font-mono">{{ $orchard->customer->phone }}</span>
                                        </div>
                                    @else
                                        <span class="text-gray-400 text-xs">—</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <div class="font-medium text-gray-900">
                                        {{ number_format($orchard->area_kanals, 2) }} <span class="text-xs text-gray-500 font-normal">Kanals</span>
                                    </div>
                                    <div class="text-xs text-gray-500 mt-0.5">
                                        {{ number_format($orchard->tree_count) }} Plants
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-gray-900 font-medium">
                                        {{ $orchard->age ?? '—' }}
                                    </div>
                                    <div class="text-xs text-gray-400 font-mono mt-0.5">
                                        {{ $orchard->date_of_establishment?->format('d M Y') ?? '—' }}
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    @if($orchard->is_company_established)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-100">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                            Established by Plant Tech Agro
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600">
                                            Self Registered
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        @if($orchard->google_maps_url)
                                            <a href="{{ $orchard->google_maps_url }}" target="_blank" rel="noopener noreferrer"
                                               class="p-1.5 text-gray-400 hover:text-brand-600 rounded-lg hover:bg-gray-100 transition" title="View GPS on Google Maps">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                            </a>
                                        @endif
                                        <x-admin.button href="{{ route('admin.orchards.show', $orchard) }}" variant="secondary" size="sm">View</x-admin.button>
                                        <x-admin.button href="{{ route('admin.orchards.edit', $orchard) }}" variant="secondary" size="sm">Edit</x-admin.button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                    <div class="flex flex-col items-center">
                                        <svg class="w-12 h-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064"/></svg>
                                        <p class="text-sm">No orchards registered yet.</p>
                                        <a href="{{ route('admin.orchards.create') }}" class="mt-2 text-sm text-brand-600 hover:text-brand-700 font-medium">Register your first orchard</a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($orchards->hasPages())
                <div class="px-6 py-4 border-t border-gray-100">
                    {{ $orchards->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
