@extends('admin.layout')

@section('page-title', 'Testimonials')

@section('content')
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Testimonials</h2>
                <p class="text-sm text-gray-500 mt-1">Reviews from farmers and orchard owners.</p>
            </div>
            <x-admin.button type="button" variant="primary" icon='<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>'>
                <a href="{{ route('admin.testimonials.create') }}">Create Testimonial</a>
            </x-admin.button>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="px-6 py-3 font-semibold text-gray-600">Name</th>
                            <th class="px-6 py-3 font-semibold text-gray-600">Role</th>
                            <th class="px-6 py-3 font-semibold text-gray-600">Rating</th>
                            <th class="px-6 py-3 font-semibold text-gray-600">Active</th>
                            <th class="px-6 py-3 font-semibold text-gray-600 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($testimonials as $testimonial)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4 font-medium text-gray-900">{{ $testimonial->name }}</td>
                                <td class="px-6 py-4 text-gray-600">{{ $testimonial->role }}</td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-0.5">
                                        @for($i = 1; $i <= 5; $i++)
                                            <svg class="w-4 h-4 {{ $i <= $testimonial->rating ? 'text-amber-400' : 'text-gray-300' }}" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                            </svg>
                                        @endfor
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    @if($testimonial->is_active)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-50 text-green-700">Active</span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-50 text-red-700">Hidden</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <x-admin.button type="button" variant="secondary" size="sm">
                                            <a href="{{ route('admin.testimonials.edit', $testimonial) }}">Edit</a>
                                        </x-admin.button>
                                        <form action="{{ route('admin.testimonials.destroy', $testimonial) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this testimonial?')">
                                            @csrf
                                            @method('DELETE')
                                            <x-admin.button type="submit" variant="danger" size="sm">Delete</x-admin.button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                    <div class="flex flex-col items-center">
                                        <svg class="w-12 h-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z"/>
                                        </svg>
                                        <p class="text-sm">No testimonials found.</p>
                                        <a href="{{ route('admin.testimonials.create') }}" class="mt-2 text-sm text-brand-600 hover:text-brand-700 font-medium">Create your first testimonial</a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($testimonials->hasPages())
                <div class="px-6 py-4 border-t border-gray-100">
                    {{ $testimonials->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
