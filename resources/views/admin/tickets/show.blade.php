@extends('admin.layout')

@section('page-title', "Ticket {$ticket->ticket_number}")

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

<div class="space-y-6" x-data="{
    activeTab: 'reply',
    imageModalOpen: false,
    modalImageUrl: '',
    modalImageTitle: '',
    openImageModal(url, title) {
        this.modalImageUrl = url;
        this.modalImageTitle = title;
        this.imageModalOpen = true;
    },
    cannedResponses: {{ Js::from($cannedResponses) }},
    applyCannedResponse(event) {
        const index = event.target.value;
        if (index !== '') {
            const item = this.cannedResponses[index];
            if (item) {
                document.getElementById('publicReplyMessage').value = item.body;
            }
        }
    }
}">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-4">
            @if($ticket->customer)
                <x-admin.avatar :name="$ticket->customer->name" size="lg" :status="$ticket->customer->status" class="shadow-xs" />
            @else
                <div class="w-14 h-14 rounded-full bg-gray-100 text-gray-500 flex items-center justify-center font-bold text-lg border border-gray-200">
                    ?
                </div>
            @endif
            <div>
                <div class="flex flex-wrap items-center gap-2.5">
                    <h2 class="text-2xl font-bold text-gray-900">{{ $ticket->ticket_number }}</h2>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium {{ $ticket->statusBadgeClasses() }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ $statusDotColors[$ticket->status] ?? 'bg-gray-400' }}"></span>
                        {{ $ticket->statusLabel() }}
                    </span>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $ticket->priorityBadgeClasses() }}">
                        {{ $ticket->priorityLabel() }} Priority
                    </span>
                    @if($ticket->creation_mode === 'photo_quick')
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg text-xs font-semibold bg-emerald-100 text-emerald-800 border border-emerald-200">
                            📷 Photo Query
                        </span>
                    @elseif($ticket->creation_mode === 'voice_quick')
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg text-xs font-semibold bg-purple-100 text-purple-800 border border-purple-200">
                            🎙️ Voice Query
                        </span>
                    @endif
                </div>
                <h1 class="text-lg font-semibold text-gray-800 mt-1">{{ $ticket->subject }}</h1>
                <div class="flex items-center gap-3 text-xs text-gray-400 mt-1">
                    <span>Opened: {{ $ticket->created_at->format('d M Y, h:i A') }}</span>
                    <span>·</span>
                    <span>Category: {{ explode('/', $ticket->categoryLabel())[0] }}</span>
                    @if($ticket->orchard)
                        <span>·</span>
                        <span class="text-brand-600 font-medium">📍 {{ $ticket->orchard->name }}</span>
                    @endif
                </div>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <x-admin.button href="{{ route('admin.tickets.index') }}" variant="secondary" icon='<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>'>
                Back
            </x-admin.button>

            @if($ticket->customer?->phone)
                @php
                    $cleanPhone = preg_replace('/[^\d]/', '', $ticket->customer->phone);
                    if (strlen($cleanPhone) === 10) $cleanPhone = '91' . $cleanPhone;
                    $waText = urlencode("Hello {$ticket->customer->name}, regarding your PTA query #{$ticket->ticket_number} ({$ticket->subject}): ");
                @endphp
                <x-admin.button href="https://wa.me/{{ $cleanPhone }}?text={{ $waText }}" target="_blank" variant="secondary">
                    <span class="mr-1.5">💬</span> WhatsApp
                </x-admin.button>
                <x-admin.button href="tel:{{ $ticket->customer->phone }}" variant="secondary">
                    <span class="mr-1.5">📞</span> Call
                </x-admin.button>
            @endif

            @if(Auth::guard('admin')->user()->hasRole('Super Admin'))
                <form method="POST" action="{{ route('admin.tickets.destroy', $ticket) }}" onsubmit="return confirm('Delete this ticket and all attachments permanently?');">
                    @csrf
                    @method('DELETE')
                    <x-admin.button type="submit" variant="danger" size="default" title="Delete Ticket">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </x-admin.button>
                </form>
            @endif
        </div>
    </div>

    {{-- Workflow Bar: Status, Priority, Staff Assignment --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
            {{-- 1. Status Update --}}
            <form method="POST" action="{{ route('admin.tickets.status', $ticket) }}" class="space-y-1.5">
                @csrf
                @method('PATCH')
                <label class="block text-sm font-medium text-gray-700">Ticket Status</label>
                <select name="status" onchange="this.form.submit()"
                        class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 transition focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                    @foreach(\App\Models\Ticket::STATUSES as $val => $lbl)
                        <option value="{{ $val }}" {{ $ticket->status === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                    @endforeach
                </select>
            </form>

            {{-- 2. Priority Update --}}
            <form method="POST" action="{{ route('admin.tickets.priority', $ticket) }}" class="space-y-1.5">
                @csrf
                @method('PATCH')
                <label class="block text-sm font-medium text-gray-700">Priority Level</label>
                <select name="priority" onchange="this.form.submit()"
                        class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 transition focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                    @foreach(\App\Models\Ticket::PRIORITIES as $val => $lbl)
                        <option value="{{ $val }}" {{ $ticket->priority === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                    @endforeach
                </select>
            </form>

            {{-- 3. Staff Assignment --}}
            <form method="POST" action="{{ route('admin.tickets.assign', $ticket) }}" class="space-y-1.5">
                @csrf
                @method('PATCH')
                <label class="block text-sm font-medium text-gray-700">Assigned Agent</label>
                <select name="assigned_to" onchange="this.form.submit()"
                        class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 transition focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                    <option value="">-- Unassigned --</option>
                    @foreach($staffList as $staff)
                        <option value="{{ $staff->id }}" {{ $ticket->assigned_to == $staff->id ? 'selected' : '' }}>
                            {{ $staff->name }} ({{ ucfirst(str_replace('_', ' ', $staff->role ?? 'Staff')) }})
                        </option>
                    @endforeach
                </select>
            </form>
        </div>
    </div>

    {{-- Main Workbench Grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Left / Center: Diagnostic Photos, Conversation Thread, Reply Editor (2 Columns) --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Diagnostic Evidence --}}
            @php
                $photos = $ticket->attachments->where('file_type', 'image');
                $audioFiles = $ticket->attachments->where('file_type', 'audio');
            @endphp

            @if($photos->count() > 0 || $audioFiles->count() > 0)
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between bg-gray-50/50">
                        <h3 class="font-semibold text-gray-900 text-sm flex items-center gap-2">
                            <span class="text-base">🔬</span>
                            <span>Uploaded Diagnostic Evidence</span>
                        </h3>
                        <span class="text-xs text-gray-500">
                            {{ $photos->count() }} photo(s) {{ $audioFiles->count() > 0 ? '+ voice note' : '' }}
                        </span>
                    </div>

                    <div class="p-6 space-y-4">
                        @if($photos->count() > 0)
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                                @foreach($photos as $photo)
                                    <div class="group relative rounded-xl border border-gray-200 overflow-hidden bg-gray-50 aspect-square cursor-pointer shadow-xs hover:shadow-md transition"
                                         @click="openImageModal('{{ $photo->url() }}', '{{ addslashes($photo->file_name) }}')">
                                        <img src="{{ $photo->url() }}" alt="{{ $photo->file_name }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                                        <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition flex items-center justify-center text-white text-xs font-semibold gap-1">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"/>
                                            </svg>
                                            <span>Zoom</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        @if($audioFiles->count() > 0)
                            @foreach($audioFiles as $audio)
                                <div class="bg-gray-50 rounded-xl p-4 border border-gray-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-full bg-purple-100 text-purple-700 flex items-center justify-center font-bold text-sm">
                                            🎙️
                                        </div>
                                        <div>
                                            <p class="text-sm font-semibold text-gray-900">Farmer Voice Note</p>
                                            <p class="text-xs text-gray-500">{{ $audio->file_name }} ({{ $audio->formattedSize() }})</p>
                                        </div>
                                    </div>
                                    <audio controls class="h-8 max-w-full">
                                        <source src="{{ $audio->url() }}">
                                        Your browser does not support audio element.
                                    </audio>
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>
            @endif

            {{-- Conversation Thread --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between bg-gray-50/50">
                    <h3 class="font-semibold text-gray-900 text-sm flex items-center gap-2">
                        <svg class="w-4 h-4 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                        </svg>
                        <span>Conversation Thread</span>
                    </h3>
                    <span class="text-xs text-gray-400">{{ $ticket->messages->count() }} message(s)</span>
                </div>

                <div class="p-6 space-y-5">
                    @forelse($ticket->messages as $msg)
                        @if($msg->sender_type === 'system')
                            {{-- System Log Entry --}}
                            <div class="flex items-center justify-center my-2">
                                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-gray-100 border border-gray-200 text-xs font-medium text-gray-600">
                                    <span>⚙️</span>
                                    <span>{{ $msg->message }}</span>
                                    <span class="text-gray-400">· {{ $msg->created_at->format('d M, h:i A') }}</span>
                                </div>
                            </div>
                        @elseif($msg->is_internal)
                            {{-- Internal Staff Note (Amber Card) --}}
                            <div class="rounded-2xl border border-amber-200 bg-amber-50/60 p-4 space-y-2">
                                <div class="flex items-center justify-between text-xs">
                                    <div class="flex items-center gap-2 font-semibold text-amber-900">
                                        <span class="px-2 py-0.5 rounded bg-amber-200 text-amber-900 text-[10px] uppercase font-bold tracking-wider">
                                            🔒 Internal Note
                                        </span>
                                        <span>{{ $msg->senderName() }}</span>
                                    </div>
                                    <span class="text-amber-700 text-xs">{{ $msg->created_at->format('d M Y, h:i A') }} ({{ $msg->created_at->diffForHumans() }})</span>
                                </div>
                                <div class="text-sm text-amber-950 whitespace-pre-wrap leading-relaxed pl-1">
                                    {{ $msg->message }}
                                </div>
                                @if($msg->attachments->count() > 0)
                                    <div class="pt-2 flex flex-wrap gap-2 border-t border-amber-200">
                                        @foreach($msg->attachments as $att)
                                            <a href="{{ $att->url() }}" target="_blank"
                                               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-amber-100 text-amber-900 text-xs hover:bg-amber-200 transition">
                                                📎 {{ $att->file_name }} ({{ $att->formattedSize() }})
                                            </a>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @elseif($msg->sender_type === 'customer')
                            {{-- Farmer Message --}}
                            <div class="rounded-2xl border border-emerald-100 bg-emerald-50/20 p-4 space-y-2">
                                <div class="flex items-center justify-between text-xs">
                                    <div class="flex items-center gap-2 font-semibold text-gray-900">
                                        <x-admin.avatar :name="$ticket->customer?->name ?? 'Farmer'" size="xs" />
                                        <span>{{ $ticket->customer?->name ?? 'Farmer' }}</span>
                                        <span class="px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 text-[10px] font-medium">Farmer</span>
                                    </div>
                                    <span class="text-gray-400 text-xs">{{ $msg->created_at->format('d M Y, h:i A') }} ({{ $msg->created_at->diffForHumans() }})</span>
                                </div>
                                <div class="text-sm text-gray-800 whitespace-pre-wrap leading-relaxed pl-8">
                                    {{ $msg->message }}
                                </div>
                                @if($msg->attachments->count() > 0)
                                    <div class="pl-8 pt-2 flex flex-wrap gap-2">
                                        @foreach($msg->attachments as $att)
                                            @if($att->isImage())
                                                <div class="w-20 h-20 rounded-xl border border-gray-200 overflow-hidden cursor-pointer shadow-xs hover:scale-105 transition"
                                                     @click="openImageModal('{{ $att->url() }}', '{{ addslashes($att->file_name) }}')">
                                                    <img src="{{ $att->url() }}" alt="{{ $att->file_name }}" class="w-full h-full object-cover">
                                                </div>
                                            @else
                                                <a href="{{ $att->url() }}" target="_blank"
                                                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white border border-gray-200 text-gray-700 text-xs hover:bg-gray-50 shadow-xs transition">
                                                    📎 {{ $att->file_name }}
                                                </a>
                                            @endif
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @else
                            {{-- Staff Public Reply --}}
                            <div class="rounded-2xl border border-brand-100 bg-brand-50/20 p-4 space-y-2">
                                <div class="flex items-center justify-between text-xs">
                                    <div class="flex items-center gap-2 font-semibold text-gray-900">
                                        <x-admin.avatar :name="$msg->senderName()" size="xs" />
                                        <span>{{ $msg->senderName() }}</span>
                                        <span class="px-2 py-0.5 rounded-full bg-brand-100 text-brand-800 text-[10px] font-medium">PTA Specialist</span>
                                    </div>
                                    <span class="text-gray-400 text-xs">{{ $msg->created_at->format('d M Y, h:i A') }} ({{ $msg->created_at->diffForHumans() }})</span>
                                </div>
                                <div class="text-sm text-gray-800 whitespace-pre-wrap leading-relaxed pl-8">
                                    {{ $msg->message }}
                                </div>
                                @if($msg->attachments->count() > 0)
                                    <div class="pl-8 pt-2 flex flex-wrap gap-2">
                                        @foreach($msg->attachments as $att)
                                            <a href="{{ $att->url() }}" target="_blank"
                                               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white border border-gray-200 text-brand-700 text-xs hover:bg-brand-50 shadow-xs transition">
                                                📎 {{ $att->file_name }} ({{ $att->formattedSize() }})
                                            </a>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @endif
                    @empty
                        <p class="text-center text-gray-400 text-sm py-6">No message history yet.</p>
                    @endforelse
                </div>
            </div>

            {{-- Reply Workspace with PTA Canonical Tabs --}}
            <div class="space-y-4">
                {{-- PTA Tab Navigation --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 px-2 py-1">
                    <nav class="flex gap-1 overflow-x-auto" aria-label="Reply tabs">
                        <button type="button" @click="activeTab = 'reply'"
                            :class="activeTab === 'reply' ? 'bg-brand-50 text-brand-700 border-brand-200' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50 border-transparent'"
                            class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-medium rounded-xl border transition-all whitespace-nowrap">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
                            Public Reply to Farmer
                        </button>
                        <button type="button" @click="activeTab = 'note'"
                            :class="activeTab === 'note' ? 'bg-amber-50 text-amber-800 border-amber-200' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50 border-transparent'"
                            class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-medium rounded-xl border transition-all whitespace-nowrap">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                            Internal Staff Note
                        </button>
                    </nav>
                </div>

                {{-- Form Card --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    {{-- Tab 1: Public Reply --}}
                    <div x-show="activeTab === 'reply'" class="space-y-5">
                        <form method="POST" action="{{ route('admin.tickets.messages.store', $ticket) }}" enctype="multipart/form-data" class="space-y-5">
                            @csrf

                            {{-- Canned Response Advisory Picker --}}
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-gray-50 rounded-xl p-4 border border-gray-100">
                                <div>
                                    <p class="text-sm font-semibold text-gray-900">Technical Advisory Templates</p>
                                    <p class="text-xs text-gray-500">Insert pre-verified pesticide, fungicide, or pruning advisories into your reply.</p>
                                </div>
                                <select @change="applyCannedResponse($event)"
                                        class="rounded-xl border border-gray-200 bg-white px-3.5 py-2 text-sm text-gray-900 transition focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                                    <option value="">-- Choose Advisory --</option>
                                    @foreach($cannedResponses as $idx => $item)
                                        <option value="{{ $idx }}">{{ $item['title'] }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="publicReplyMessage" class="block text-sm font-medium text-gray-700 mb-1.5">
                                    Reply Message <span class="text-red-500">*</span>
                                </label>
                                <textarea id="publicReplyMessage" name="message" rows="5" required
                                          placeholder="Type technical advice, prescription, or visit schedule here..."
                                          class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 placeholder-gray-400 transition focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100 resize-y"></textarea>
                            </div>

                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pt-2">
                                <div class="flex-1 space-y-2">
                                    <label class="block text-sm font-medium text-gray-700">Attach Prescription Sheet / Guide:</label>
                                    <input type="file" name="attachment"
                                           class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2 text-sm text-gray-900 transition file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-sm file:font-medium file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100 focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                                    <label class="inline-flex items-center gap-2 text-xs text-gray-600 cursor-pointer pt-1">
                                        <input type="checkbox" name="mark_awaiting" value="1" checked class="rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                                        <span>Mark status as "Awaiting Farmer"</span>
                                    </label>
                                </div>

                                <x-admin.button type="submit" variant="primary">
                                    Send Reply
                                </x-admin.button>
                            </div>
                        </form>
                    </div>

                    {{-- Tab 2: Internal Note --}}
                    <div x-show="activeTab === 'note'" x-cloak class="space-y-5">
                        <form method="POST" action="{{ route('admin.tickets.notes.store', $ticket) }}" enctype="multipart/form-data" class="space-y-5">
                            @csrf
                            <div>
                                <label class="block text-sm font-medium text-amber-900 mb-1.5">
                                    Internal Note (Private to Staff & Agronomists) <span class="text-red-500">*</span>
                                </label>
                                <textarea name="note" rows="4" required
                                          placeholder="Enter internal diagnostic notes, tree history, or coordinator instructions..."
                                          class="w-full rounded-xl border border-amber-200 bg-amber-50/30 px-4 py-2.5 text-sm text-gray-900 placeholder-gray-400 transition focus:outline-none focus:border-amber-500 focus:ring-2 focus:ring-amber-200 resize-y"></textarea>
                            </div>

                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pt-2">
                                <div class="flex-1 space-y-2">
                                    <label class="block text-sm font-medium text-gray-700">Attach Internal Document:</label>
                                    <input type="file" name="attachment"
                                           class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2 text-sm text-gray-900 transition file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-sm file:font-medium file:bg-amber-100 file:text-amber-900 hover:file:bg-amber-200 focus:outline-none focus:border-amber-500 focus:ring-2 focus:ring-amber-200">
                                </div>

                                <x-admin.button type="submit" variant="primary">
                                    Save Note
                                </x-admin.button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right Column: Farmer Card, Orchard Details, Ticket Meta (1 Column) --}}
        <div class="space-y-6">

            {{-- Farmer Profile Card matching customers/show.blade.php ethics --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                    <h3 class="text-lg font-semibold text-gray-900">Farmer Details</h3>
                    @if($ticket->customer)
                        <a href="{{ route('admin.customers.show', $ticket->customer) }}" class="text-xs font-medium text-brand-600 hover:text-brand-700">
                            View Profile →
                        </a>
                    @endif
                </div>

                @if($ticket->customer)
                    <div class="flex items-center gap-3">
                        <x-admin.avatar :name="$ticket->customer->name" size="md" :status="$ticket->customer->status" />
                        <div>
                            <p class="font-bold text-gray-900 text-base">{{ $ticket->customer->name }}</p>
                            @if($ticket->customer->orchardist_id)
                                <span class="font-mono text-xs font-bold text-indigo-700 bg-indigo-50 px-2 py-0.5 rounded border border-indigo-200">
                                    {{ $ticket->customer->orchardist_id }}
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="space-y-2.5 text-xs pt-1">
                        <div class="flex items-center justify-between text-gray-600">
                            <span class="text-gray-400">Phone:</span>
                            <span class="font-semibold text-gray-900">{{ $ticket->customer->phone ?? '—' }}</span>
                        </div>
                        <div class="flex items-center justify-between text-gray-600">
                            <span class="text-gray-400">Email:</span>
                            <span class="text-gray-800">{{ $ticket->customer->email ?? '—' }}</span>
                        </div>
                        <div class="flex items-center justify-between text-gray-600">
                            <span class="text-gray-400">Location:</span>
                            <span class="font-medium text-gray-800">{{ $ticket->customer->area ?? $ticket->customer->address ?? 'Kashmir' }}</span>
                        </div>
                        <div class="flex items-center justify-between text-gray-600">
                            <span class="text-gray-400">Orchards:</span>
                            <span class="font-semibold text-brand-700">{{ $ticket->customer->orchards->count() }} registered</span>
                        </div>
                    </div>
                @else
                    <p class="text-xs text-gray-400 italic">Farmer details not linked.</p>
                @endif
            </div>

            {{-- Target Orchard Card (if linked) --}}
            @if($ticket->orchard)
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-3">
                    <div class="flex items-center justify-between pb-2 border-b border-gray-100">
                        <h3 class="text-lg font-semibold text-gray-900">Target Orchard</h3>
                        <span class="text-xs text-brand-600 font-semibold">📍 {{ $ticket->orchard->name }}</span>
                    </div>
                    <div class="space-y-2.5 text-xs">
                        <div class="flex items-center justify-between">
                            <span class="text-gray-400">Area:</span>
                            <span class="font-semibold text-gray-900">{{ $ticket->orchard->area_kanals ?? '—' }} Kanals</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-gray-400">Location:</span>
                            <span class="text-gray-800">{{ $ticket->orchard->location ?? '—' }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-gray-400">Tree Count:</span>
                            <span class="text-gray-800">{{ $ticket->orchard->tree_count ?? '—' }} trees</span>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Ticket Metadata Card --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-3 text-xs">
                <h3 class="text-lg font-semibold text-gray-900 pb-2 border-b border-gray-100">Ticket Details</h3>

                <div class="flex items-center justify-between">
                    <span class="text-gray-400">Category:</span>
                    <span class="font-semibold text-gray-800">{{ explode('/', $ticket->categoryLabel())[0] }}</span>
                </div>

                <div class="flex items-center justify-between">
                    <span class="text-gray-400">Creation Mode:</span>
                    <span class="font-medium text-gray-700 capitalize">{{ str_replace('_', ' ', $ticket->creation_mode) }}</span>
                </div>

                <div class="flex items-center justify-between">
                    <span class="text-gray-400">Created At:</span>
                    <span class="text-gray-800">{{ $ticket->created_at->format('d M Y, h:i A') }}</span>
                </div>

                <div class="flex items-center justify-between">
                    <span class="text-gray-400">Last Activity:</span>
                    <span class="text-gray-800">{{ $ticket->last_reply_at ? $ticket->last_reply_at->format('d M, h:i A') : $ticket->created_at->format('d M, h:i A') }}</span>
                </div>

                @if($ticket->resolved_at)
                    <div class="flex items-center justify-between text-emerald-700 font-medium">
                        <span>Resolved At:</span>
                        <span>{{ $ticket->resolved_at->format('d M Y, h:i A') }}</span>
                    </div>
                @endif
            </div>

        </div>
    </div>

    {{-- Image Lightbox Modal --}}
    <div x-show="imageModalOpen" x-cloak
         class="fixed inset-0 z-50 overflow-y-auto bg-black/80 flex items-center justify-center p-4"
         @click.self="imageModalOpen = false">
        <div class="relative bg-white rounded-2xl max-w-4xl max-h-[90vh] overflow-hidden shadow-2xl flex flex-col">
            <div class="px-5 py-3 border-b border-gray-100 flex items-center justify-between bg-gray-50">
                <p class="font-semibold text-sm text-gray-800 truncate" x-text="modalImageTitle"></p>
                <button @click="imageModalOpen = false" class="text-gray-400 hover:text-gray-700 p-1">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div class="p-2 flex-1 overflow-auto flex items-center justify-center bg-gray-900">
                <img :src="modalImageUrl" class="max-h-[75vh] w-auto object-contain rounded-lg">
            </div>
            <div class="px-5 py-3 bg-gray-50 border-t border-gray-100 flex items-center justify-between text-xs">
                <a :href="modalImageUrl" target="_blank" download class="text-brand-600 font-semibold hover:underline">
                    Download High-Res Original ⬇
                </a>
                <button @click="imageModalOpen = false" class="px-3 py-1 bg-gray-200 hover:bg-gray-300 rounded-lg text-gray-800">
                    Close
                </button>
            </div>
        </div>
    </div>

</div>
@endsection
