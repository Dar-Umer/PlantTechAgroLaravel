@extends('admin.layout')

@section('page-title', 'Edit Staff')

@section('content')
    <div class="space-y-6 max-w-3xl">
        <div class="flex items-center justify-between">
            <h2 class="text-2xl font-bold text-gray-900">Edit Staff</h2>
            <x-admin.button href="{{ route('admin.staff.index') }}" variant="secondary" icon='<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>'>Back</x-admin.button>
        </div>

        <form action="{{ route('admin.staff.update', $staff) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')
            @include('admin.staff._form', ['submitLabel' => 'Update Staff Member', 'passwordRequired' => false])
        </form>
    </div>
@endsection
