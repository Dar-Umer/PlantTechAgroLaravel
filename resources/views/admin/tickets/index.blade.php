@extends('admin.layout')

@section('page-title', 'Support Tickets')

@section('content')
@php
    $statusDotColors = [
        'open' => 'bg-emerald-500',
        'in_progress' => 'bg-blue-500',
        'awaiting_farmer' => 'bg-amber-500',
        'resolved' => 'bg-purple-500',
        'closed' => 'bg-gray-400',
    ];
@endphp

<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Support Tickets</h2>
            <p class="text-sm text-gray-500 mt-1">Resolve farmer queries, photo diagnoses, and technical advisories.</p>
        </div>
        <div class="flex items-center gap-3">
            <x-admin.button href="{{ route('admin.tickets.create') }}" variant="primary" icon='<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>'>
                Create Ticket
            </x-admin.button>
        </div>
    </div>

    {{-- Status Summary KPIs --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-7 gap-3">
        <a href="{{ route('admin.tickets.index') }}"
           class="bg-white rounded-2xl shadow-sm border p-4 text-center transition {{ !request('status') && !request('assigned_to') ? 'border-brand-400 ring-2 ring-brand-100' : 'border-gray-100 hover:border-brand-200' }}">
            <p class="text-2xl font-bold text-gray-900">{{ $metrics['total'] }}</p>
            <p class="text-xs text-gray-500 mt-0.5 font-medium">All Tickets</p>
        </a>

        <a href="{{ route('admin.tickets.index', ['status' => 'open']) }}"
           class="bg-white rounded-2xl shadow-sm border p-4 text-center transition {{ request('status') === 'open' ? 'border-emerald-400 ring-2 ring-emerald-100' : 'border-gray-100 hover:border-emerald-200' }}">
            <p class="text-2xl font-bold text-emerald-600">{{ $metrics['open'] }}</p>
            <p class="text-xs text-gray-500 mt-0.5 font-medium">Open / New</p>
        </a>

        <a href="{{ route('admin.tickets.index', ['status' => 'in_progress']) }}"
           class="bg-white rounded-2xl shadow-sm border p-4 text-center transition {{ request('status') === 'in_progress' ? 'border-blue-400 ring-2 ring-blue-100' : 'border-gray-100 hover:border-blue-200' }}">
            <p class="text-2xl font-bold text-blue-600">{{ $metrics['in_progress'] }}</p>
            <p class="text-xs text-gray-500 mt-0.5 font-medium">In Progress</p>
        </a>

        <a href="{{ route('admin.tickets.index', ['status' => 'awaiting_farmer']) }}"
           class="bg-white rounded-2xl shadow-sm border p-4 text-center transition {{ request('status') === 'awaiting_farmer' ? 'border-amber-400 ring-2 ring-amber-100' : 'border-gray-100 hover:border-amber-200' }}">
            <p class="text-2xl font-bold text-amber-600">{{ $metrics['awaiting_farmer'] }}</p>
            <p class="text-xs text-gray-500 mt-0.5 font-medium">Awaiting Farmer</p>
        </a>

        <a href="{{ route('admin.tickets.index', ['status' => 'resolved']) }}"
           class="bg-white rounded-2xl shadow-sm border p-4 text-center transition {{ request('status') === 'resolved' ? 'border-purple-400 ring-2 ring-purple-100' : 'border-gray-100 hover:border-purple-200' }}">
            <p class="text-2xl font-bold text-purple-600">{{ $metrics['resolved'] }}</p>
            <p class="text-xs text-gray-500 mt-0.5 font-medium">Resolved</p>
        </a>

        <a href="{{ route('admin.tickets.index', ['assigned_to' => 'unassigned']) }}"
           class="bg-white rounded-2xl shadow-sm border p-4 text-center transition {{ request('assigned_to') === 'unassigned' ? 'border-rose-400 ring-2 ring-rose-100' : 'border-gray-100 hover:border-rose-200' }}">
            <p class="text-2xl font-bold text-rose-600">{{ $metrics['unassigned'] }}</p>
            <p class="text-xs text-gray-500 mt-0.5 font-medium">⚠️ Unassigned</p>
        </a>

        <a href="{{ route('admin.tickets.index', ['assigned_to' => 'my_tickets']) }}"
           class="bg-white rounded-2xl shadow-sm border p-4 text-center transition {{ request('assigned_to') === 'my_tickets' ? 'border-brand-500 ring-2 ring-brand-100 bg-brand-50/20' : 'border-gray-100 hover:border-brand-200' }}">
            <p class="text-2xl font-bold text-brand-700">{{ $metrics['my_tickets'] }}</p>
            <p class="text-xs text-brand-700 mt-0.5 font-semibold">Assigned To Me</p>
        </a>
    </div>

    {{-- Filter Toolbar matching project standards --}}
    <form method="GET" action="{{ route('admin.tickets.index') }}" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 flex flex-wrap items-center gap-3">
        <div class="flex-1 min-w-[220px]">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Search by Ticket #, farmer name, phone..."
                   class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 placeholder-gray-400 transition focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
        </div>

        <div>
            <select name="status" class="rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 transition focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                <option value="all">All Statuses</option>
                @foreach(\App\Models\Ticket::STATUSES as $val => $lbl)
                    <option value="{{ $val }}" {{ request('status') === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <select name="priority" class="rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 transition focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                <option value="all">All Priorities</option>
                @foreach(\App\Models\Ticket::PRIORITIES as $val => $lbl)
                    <option value="{{ $val }}" {{ request('priority') === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <select name="category" class="rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 transition focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                <option value="all">All Categories</option>
                @foreach(\App\Models\Ticket::CATEGORIES as $val => $lbl)
                    <option value="{{ $val }}" {{ request('category') === $val ? 'selected' : '' }}>{{ explode('/', $lbl)[0] }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <select name="assigned_to" class="rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 transition focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                <option value="all">All Staff</option>
                <option value="unassigned" {{ request('assigned_to') === 'unassigned' ? 'selected' : '' }}>⚠️ Unassigned Only</option>
                <option value="my_tickets" {{ request('assigned_to') === 'my_tickets' ? 'selected' : '' }}>👤 Assigned to Me</option>
                <optgroup label="Specific Staff">
                    @foreach($staffList as $staff)
                        <option value="{{ $staff->id }}" {{ request('assigned_to') == $staff->id ? 'selected' : '' }}>{{ $staff->name }}</option>
                    @endforeach
                </optgroup>
            </select>
        </div>

        <x-admin.button type="submit" variant="primary">Filter</x-admin.button>

        @if(request()->hasAny(['q', 'status', 'priority', 'category', 'assigned_to']))
            <a href="{{ route('admin.tickets.index') }}" class="text-sm text-gray-500 hover:text-gray-700 ml-1">Clear</a>
        @endif
    </form>

    {{-- Tickets Table --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-gray-50/80 border-b border-gray-100">
                    <tr>
                        <th class="px-6 py-3.5 text-xs font-semibold uppercase tracking-wider text-gray-500">Ticket</th>
                        <th class="px-6 py-3.5 text-xs font-semibold uppercase tracking-wider text-gray-500">Farmer & Orchard</th>
                        <th class="px-6 py-3.5 text-xs font-semibold uppercase tracking-wider text-gray-500">Category & Subject</th>
                        <th class="px-6 py-3.5 text-xs font-semibold uppercase tracking-wider text-gray-500 text-center">Priority</th>
                        <th class="px-6 py-3.5 text-xs font-semibold uppercase tracking-wider text-gray-500 text-center">Status</th>
                        <th class="px-6 py-3.5 text-xs font-semibold uppercase tracking-wider text-gray-500">Assigned Agent</th>
                        <th class="px-6 py-3.5 text-xs font-semibold uppercase tracking-wider text-gray-500">Last Activity</th>
                        <th class="px-6 py-3.5 text-xs font-semibold uppercase tracking-wider text-gray-500 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($tickets as $ticket)
                        <tr class="hover:bg-gray-50/70 transition group">
                            {{-- Ticket Number & Quick Mode Badge --}}
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('admin.tickets.show', $ticket) }}" class="font-bold text-gray-900 group-hover:text-brand-600 transition-colors">
                                        {{ $ticket->ticket_number }}
                                    </a>
                                    @if($ticket->creation_mode === 'photo_quick')
                                        <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-medium bg-emerald-100 text-emerald-800" title="Submitted via 1-click Photo Upload">
                                            📷 Photo
                                        </span>
                                    @elseif($ticket->creation_mode === 'voice_quick')
                                        <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-medium bg-purple-100 text-purple-800" title="Submitted via Voice Note">
                                            🎙️ Voice
                                        </span>
                                    @endif
                                </div>
                            </td>

                            {{-- Farmer Details with Standard Component Avatar --}}
                            <td class="px-6 py-4">
                                @if($ticket->customer)
                                    <div class="flex items-center gap-3">
                                        <x-admin.avatar :name="$ticket->customer->name" size="sm" :status="$ticket->customer->status" class="group-hover:scale-105 transition-transform" />
                                        <div class="min-w-0">
                                            <a href="{{ route('admin.customers.show', $ticket->customer) }}" class="font-semibold text-gray-900 group-hover:text-brand-600 transition-colors">
                                                {{ $ticket->customer->name }}
                                            </a>
                                            <div class="flex items-center gap-1.5 mt-0.5">
                                                <span class="text-xs text-gray-500">{{ $ticket->customer->phone ?? '—' }}</span>
                                                @if($ticket->customer->orchardist_id)
                                                    <span class="text-gray-300">·</span>
                                                    <span class="inline-flex items-center font-mono text-[11px] font-bold text-indigo-700 bg-indigo-50 px-1.5 py-0.5 rounded border border-indigo-100">
                                                        {{ $ticket->customer->orchardist_id }}
                                                    </span>
                                                @endif
                                            </div>
                                            @if($ticket->orchard)
                                                <div class="text-[11px] text-brand-600 mt-0.5">
                                                    📍 {{ $ticket->orchard->name }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @else
                                    <span class="text-gray-400">Guest / Unknown</span>
                                @endif
                            </td>

                            {{-- Category & Subject --}}
                            <td class="px-6 py-4 max-w-xs">
                                <div class="text-xs font-medium text-gray-500 mb-0.5">
                                    {{ explode('/', $ticket->categoryLabel())[0] }}
                                </div>
                                <a href="{{ route('admin.tickets.show', $ticket) }}" class="font-semibold text-gray-900 group-hover:text-brand-600 line-clamp-1 transition-colors">
                                    {{ $ticket->subject }}
                                </a>
                                @if($ticket->attachments->count() > 0)
                                    <div class="text-[11px] text-gray-400 mt-1 flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                                        </svg>
                                        {{ $ticket->attachments->count() }} attachment(s)
                                    </div>
                                @endif
                            </td>

                            {{-- Priority Badge --}}
                            <td class="px-6 py-4 text-center whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $ticket->priorityBadgeClasses() }}">
                                    {{ $ticket->priorityLabel() }}
                                </span>
                            </td>

                            {{-- Status Badge with subtle pulse dot matching project ethics --}}
                            <td class="px-6 py-4 text-center whitespace-nowrap">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium {{ $ticket->statusBadgeClasses() }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $statusDotColors[$ticket->status] ?? 'bg-gray-400' }}"></span>
                                    {{ $ticket->statusLabel() }}
                                </span>
                            </td>

                            {{-- Assigned Staff --}}
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($ticket->assignedStaff)
                                    <div class="flex items-center gap-2">
                                        <x-admin.avatar :name="$ticket->assignedStaff->name" size="xs" />
                                        <div>
                                            <p class="text-xs font-semibold text-gray-900">{{ $ticket->assignedStaff->name }}</p>
                                            <p class="text-[10px] text-gray-400">{{ ucfirst(str_replace('_', ' ', $ticket->assignedStaff->role ?? 'Staff')) }}</p>
                                        </div>
                                    </div>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-semibold bg-rose-50 text-rose-700 border border-rose-200">
                                        Unassigned
                                    </span>
                                @endif
                            </td>

                            {{-- Last Activity --}}
                            <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-500">
                                <p>{{ $ticket->last_reply_at ? $ticket->last_reply_at->diffForHumans() : $ticket->created_at->diffForHumans() }}</p>
                                <p class="text-[11px] text-gray-400">by {{ $ticket->last_reply_by ? ucfirst($ticket->last_reply_by) : 'Farmer' }}</p>
                            </td>

                            {{-- Actions --}}
                            <td class="px-6 py-4 text-right whitespace-nowrap">
                                <x-admin.button href="{{ route('admin.tickets.show', $ticket) }}" variant="secondary" size="sm">
                                    View
                                </x-admin.button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                                <div class="max-w-sm mx-auto space-y-3">
                                    <div class="w-12 h-12 rounded-full bg-brand-50 text-brand-600 flex items-center justify-center mx-auto">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                                        </svg>
                                    </div>
                                    <p class="font-semibold text-gray-900">No support tickets found</p>
                                    <p class="text-xs text-gray-500">When farmers submit questions or crop photos through the Flutter mobile app, they will appear here in real-time.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($tickets->hasPages())
            <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/50">
                {{ $tickets->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
