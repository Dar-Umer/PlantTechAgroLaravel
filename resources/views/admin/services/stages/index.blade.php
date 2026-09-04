@extends('admin.layout')

@section('page-title', 'Service Stages — ' . $service->name)

@section('content')
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Service Stages</h2>
                <p class="text-sm text-gray-500 mt-1">Step-by-step workflow of <span class="font-medium text-gray-700">{{ $service->name }}</span>, from start to completion. Field Agents will complete these stages for customers.</p>
            </div>
            <div class="flex items-center gap-2">
                <x-admin.button href="{{ route('admin.services.edit', $service) }}" variant="secondary" icon='<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>'>Back to Service</x-admin.button>
                <x-admin.button href="{{ route('admin.services.stages.create', $service) }}" variant="primary" icon='<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>'>Add Stage</x-admin.button>
            </div>
        </div>

        <div class="space-y-3">
            @forelse($stages as $index => $stage)
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 flex items-start gap-4">
                    <div class="w-9 h-9 rounded-full bg-brand-600 text-white flex items-center justify-center font-bold text-sm flex-shrink-0">
                        {{ $index + 1 }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-3 flex-wrap">
                            <h3 class="font-semibold text-gray-900">{{ $stage->name }}</h3>
                            <span class="text-xs text-gray-400">Order: {{ $stage->sort_order }}</span>
                            @if($stage->requires_photo)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-50 text-blue-700">
                                    📷 Photo required{{ $stage->min_photos > 1 ? ' (min ' . $stage->min_photos . ')' : '' }}
                                </span>
                            @endif
                            @if($stage->requires_pdf)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-purple-50 text-purple-700">
                                    📄 PDF required
                                </span>
                            @endif
                        </div>
                        @if($stage->description)
                            <p class="text-sm text-gray-600 mt-1">{{ $stage->description }}</p>
                        @endif
                    </div>
                    <div class="flex items-center gap-2 flex-shrink-0">
                        <x-admin.button href="{{ route('admin.stage-products.index', $stage) }}" variant="secondary" size="sm">Materials ({{ $stage->products()->count() }})</x-admin.button>
                        <x-admin.button href="{{ route('admin.services.stages.edit', [$service, $stage]) }}" variant="secondary" size="sm">Edit</x-admin.button>
                        <form action="{{ route('admin.services.stages.destroy', [$service, $stage]) }}" method="POST" onsubmit="return confirm('Delete this stage?')">
                            @csrf
                            @method('DELETE')
                            <x-admin.button type="submit" variant="danger" size="sm">Delete</x-admin.button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 px-6 py-12 text-center text-gray-500">
                    <p class="text-sm">No stages defined yet. Click "Add Stage" to build the workflow for this service.</p>
                </div>
            @endforelse
        </div>
    </div>
@endsection
