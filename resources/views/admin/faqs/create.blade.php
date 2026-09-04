@extends('admin.layout')

@section('page-title', 'Create FAQ')

@section('content')
    <div class="space-y-6 max-w-3xl">
        <div class="flex items-center justify-between">
            <h2 class="text-2xl font-bold text-gray-900">Create FAQ</h2>
            <x-admin.button href="{{ route('admin.faqs.index') }}" variant="secondary" icon='<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>'>Back</x-admin.button>
        </div>

        <form action="{{ route('admin.faqs.store') }}" method="POST" class="space-y-6">
            @csrf
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <div class="space-y-5">
                    <x-admin.input name="question" label="Question" :value="old('question')" required placeholder="e.g. What services does your company offer?" />
                    <x-admin.textarea name="answer" label="Answer" :value="old('answer')" rows="6" required />
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <x-admin.input name="category" label="Category" :value="old('category')" placeholder="e.g. general, plants, irrigation" />
                        <x-admin.input name="sort_order" label="Sort Order" type="number" :value="old('sort_order', 0)" />
                    </div>
                    <x-admin.checkbox name="is_active" label="Active" :checked="old('is_active', true)" help="Visible to visitors when active." />
                </div>
            </div>

            <div class="flex justify-end">
                <x-admin.button type="submit">Create FAQ</x-admin.button>
            </div>
        </form>
    </div>
@endsection
