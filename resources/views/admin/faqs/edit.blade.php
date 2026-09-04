@extends('admin.layout')

@section('page-title', 'Edit FAQ')

@section('content')
    <div class="space-y-6 max-w-3xl">
        <div class="flex items-center justify-between">
            <h2 class="text-2xl font-bold text-gray-900">Edit FAQ</h2>
            <x-admin.button type="button" variant="secondary" icon='<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>'>
                <a href="{{ route('admin.faqs.index') }}">Back</a>
            </x-admin.button>
        </div>

        <form action="{{ route('admin.faqs.update', $faq) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <div class="space-y-5">
                    <x-admin.input name="question" label="Question" :value="old('question', $faq->question)" required />
                    <x-admin.textarea name="answer" label="Answer" :value="old('answer', $faq->answer)" rows="6" required />
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <x-admin.input name="category" label="Category" :value="old('category', $faq->category)" />
                        <x-admin.input name="sort_order" label="Sort Order" type="number" :value="old('sort_order', $faq->sort_order)" />
                    </div>
                    <x-admin.checkbox name="is_active" label="Active" :checked="old('is_active', $faq->is_active)" />
                </div>
            </div>

            <div class="flex justify-end">
                <x-admin.button type="submit">Update FAQ</x-admin.button>
            </div>
        </form>
    </div>
@endsection
