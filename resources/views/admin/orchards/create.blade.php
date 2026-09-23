@extends('admin.layout')

@section('page-title', 'Register Orchard')

@section('content')
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Register Orchard</h2>
                <p class="text-sm text-gray-500 mt-1">Register a new high-density or traditional farm holding under an orchardist account.</p>
            </div>
            <x-admin.button href="{{ route('admin.orchards.index') }}" variant="secondary" icon='<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>'>Back</x-admin.button>
        </div>

        <form action="{{ route('admin.orchards.store') }}" method="POST" class="space-y-6">
            @csrf
            @include('admin.orchards._form', ['submitLabel' => 'Register Orchard'])
        </form>
    </div>
@endsection
