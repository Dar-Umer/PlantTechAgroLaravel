@extends('admin.layout')

@section('page-title', 'Edit Orchard — ' . $orchard->name)

@section('content')
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Edit Orchard</h2>
                <p class="text-sm text-gray-500 mt-1">Update specifications and details for <span class="font-mono font-medium text-brand-600">{{ $orchard->orchard_id }}</span>.</p>
            </div>
            <div class="flex items-center gap-2">
                <x-admin.button href="{{ route('admin.orchards.show', $orchard) }}" variant="secondary" icon='<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>'>Back</x-admin.button>
            </div>
        </div>

        <form action="{{ route('admin.orchards.update', $orchard) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')
            @include('admin.orchards._form', ['submitLabel' => 'Update Orchard'])
        </form>
    </div>
@endsection
