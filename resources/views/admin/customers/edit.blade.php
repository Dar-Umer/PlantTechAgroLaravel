@extends('admin.layout')

@section('page-title', 'Edit Customer')

@section('content')
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <h2 class="text-2xl font-bold text-gray-900">Edit Customer</h2>
            <x-admin.button href="{{ route('admin.customers.show', $customer) }}" variant="secondary" icon='<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>'>Back</x-admin.button>
        </div>

        <form action="{{ route('admin.customers.update', $customer) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')
            @include('admin.customers._form', ['submitLabel' => 'Update Customer', 'passwordRequired' => false])
        </form>
    </div>
@endsection
