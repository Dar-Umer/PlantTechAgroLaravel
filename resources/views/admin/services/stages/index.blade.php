@extends('admin.layout')

@section('page-title', 'Service Stages Workflow — ' . $service->name)

@section('content')
{{-- Load SortableJS directly to guarantee availability --}}
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>

<div class="space-y-6" x-data="stageKanban(@js($service->id), @js(route('admin.services.stages.reorder', $service)))" x-init="init()">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-3">
                <h2 class="text-2xl font-bold text-gray-900">Service Stages Workflow</h2>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 mr-1.5 animate-pulse"></span>
                    Drag & Drop Active
                </span>
            </div>
            <p class="text-sm text-gray-500 mt-1">
                Workflow steps for <span class="font-bold text-gray-800">{{ $service->name }}</span>. Drag cards or use the <span class="font-semibold text-gray-700">&larr; &rarr;</span> buttons to reorder stages.
            </p>
        </div>

        <div class="flex items-center gap-3">
            {{-- View Toggle (Board vs List) --}}
            <div class="bg-gray-100 p-1 rounded-xl flex items-center border border-gray-200 text-xs">
                <button type="button" @click="viewMode = 'kanban'"
                        :class="viewMode === 'kanban' ? 'bg-white font-bold text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-700'"
                        class="px-3 py-1.5 rounded-lg transition flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/></svg>
                    Kanban Board
                </button>
                <button type="button" @click="viewMode = 'list'"
                        :class="viewMode === 'list' ? 'bg-white font-bold text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-700'"
                        class="px-3 py-1.5 rounded-lg transition flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                    List View
                </button>
            </div>

            <x-admin.button href="{{ route('admin.services.edit', $service) }}" variant="secondary" icon='<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>'>Service Details</x-admin.button>
            <x-admin.button href="{{ route('admin.services.stages.create', $service) }}" variant="primary" icon='<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>'>Add Stage</x-admin.button>
        </div>
    </div>

    {{-- Floating Toast Notification --}}
    <div x-show="toast.show" x-cloak
         x-transition:enter="transition ease-out duration-300 transform"
         x-transition:enter-start="opacity-0 translate-y-3 scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
         x-transition:leave="transition ease-in duration-200 transform"
         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
         x-transition:leave-end="opacity-0 translate-y-3 scale-95"
         class="fixed bottom-6 right-6 z-50 flex items-center gap-2.5 px-4 py-3 bg-gray-900/95 backdrop-blur text-white rounded-2xl shadow-2xl text-xs font-semibold border border-gray-800">
        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
        <svg class="w-4 h-4 text-emerald-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        <span x-text="toast.message"></span>
    </div>

    {{-- 1. KANBAN BOARD VIEW --}}
    <div x-show="viewMode === 'kanban'" class="space-y-4">
        <div class="flex items-center justify-between text-xs text-gray-500">
            <span class="flex items-center gap-1.5">
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/></svg>
                Drag cards horizontally or use arrows to rearrange the sequence.
            </span>
            <span class="font-medium text-gray-400" x-text="stageCount + ' total stage(s)'"></span>
        </div>

        <div class="overflow-x-auto pb-6 pt-1">
            <div id="kanban-stages-board" class="flex items-stretch gap-4 min-w-max">
                @forelse($stages as $index => $stage)
                    <div class="kanban-stage-card w-80 bg-white rounded-2xl shadow-sm border border-gray-200 hover:border-brand-500 hover:shadow-md transition-all flex flex-col justify-between flex-shrink-0 cursor-grab active:cursor-grabbing select-none group"
                         data-id="{{ $stage->id }}"
                         draggable="true"
                         @dragstart="onNativeDragStart($event)"
                         @dragover="onNativeDragOver($event)"
                         @drop="onNativeDrop($event)"
                         @dragend="onNativeDragEnd($event)">
                        
                        {{-- Card Header with Step Badge & 1-Click Reorder Arrows --}}
                        <div class="p-3.5 border-b border-gray-100 flex items-center justify-between bg-gradient-to-r from-gray-50 to-white rounded-t-2xl">
                            <div class="flex items-center gap-2">
                                <span class="stage-step-badge w-7 h-7 rounded-lg bg-brand-600 text-white font-extrabold text-xs flex items-center justify-center shadow-sm">
                                    {{ $index + 1 }}
                                </span>
                                <span class="stage-step-title text-xs font-bold text-gray-800">Stage {{ $index + 1 }}</span>
                            </div>

                            {{-- Reorder Arrows + Grip Icon --}}
                            <div class="flex items-center gap-1">
                                <button type="button" @click.stop="moveStage({{ $stage->id }}, 'left')" title="Move Earlier" class="p-1 rounded text-gray-400 hover:text-brand-600 hover:bg-brand-50 transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                                </button>
                                <button type="button" @click.stop="moveStage({{ $stage->id }}, 'right')" title="Move Later" class="p-1 rounded text-gray-400 hover:text-brand-600 hover:bg-brand-50 transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </button>
                                <div class="text-gray-300 group-hover:text-gray-500 transition px-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"/></svg>
                                </div>
                            </div>
                        </div>

                        {{-- Card Content --}}
                        <div class="p-4 space-y-3 flex-1 flex flex-col justify-between">
                            <div>
                                <h3 class="font-bold text-gray-900 text-sm group-hover:text-brand-600 transition leading-snug">{{ $stage->name }}</h3>
                                @if($stage->description)
                                    <p class="text-xs text-gray-500 mt-1 line-clamp-3 leading-relaxed">{{ $stage->description }}</p>
                                @else
                                    <p class="text-xs text-gray-400 italic mt-1">No additional description</p>
                                @endif
                            </div>

                            {{-- Requirements & Materials Badges --}}
                            <div class="flex flex-wrap gap-1.5 pt-2 border-t border-gray-50">
                                @if($stage->requires_photo)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium bg-blue-50 text-blue-700 border border-blue-100">
                                        📷 Photo{{ $stage->min_photos > 1 ? ' (min ' . $stage->min_photos . ')' : '' }}
                                    </span>
                                @endif
                                @if($stage->requires_pdf)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium bg-purple-50 text-purple-700 border border-purple-100">
                                        📄 Signoff PDF
                                    </span>
                                @endif
                                <a href="{{ route('admin.stage-products.index', $stage) }}" class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-100 hover:bg-emerald-100 transition">
                                    📦 {{ $stage->products()->count() }} Materials
                                </a>
                            </div>
                        </div>

                        {{-- Card Actions Footer --}}
                        <div class="p-3 bg-gray-50 rounded-b-2xl border-t border-gray-100 flex items-center justify-between text-xs">
                            <a href="{{ route('admin.stage-products.index', $stage) }}" class="font-semibold text-brand-600 hover:text-brand-800 transition">
                                Manage Materials &rarr;
                            </a>
                            <div class="flex items-center gap-1.5">
                                <a href="{{ route('admin.services.stages.edit', $stage) }}" class="px-2.5 py-1 rounded-lg bg-white border border-gray-200 text-gray-700 hover:bg-gray-100 font-medium transition shadow-2xs">
                                    Edit
                                </a>
                                <form action="{{ route('admin.services.stages.destroy', $stage) }}" method="POST" onsubmit="return confirm('Delete this stage?');" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1 text-gray-400 hover:text-red-600 rounded transition" title="Delete Stage">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="w-full bg-white rounded-2xl p-12 text-center border border-gray-200 text-gray-400">
                        <svg class="w-12 h-12 mx-auto mb-2 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        <p class="text-sm font-medium">No stages created for this service.</p>
                        <a href="{{ route('admin.services.stages.create', $service) }}" class="mt-2 inline-block text-xs font-bold text-brand-600 hover:underline">+ Add the first stage</a>
                    </div>
                @endforelse

                @if($stages->isNotEmpty())
                    {{-- Quick Add Next Stage Column --}}
                    <a href="{{ route('admin.services.stages.create', $service) }}"
                       class="w-72 border-2 border-dashed border-gray-300 hover:border-brand-500 bg-gray-50/50 hover:bg-brand-50/30 rounded-2xl p-6 flex flex-col items-center justify-center text-center text-gray-400 hover:text-brand-600 transition group flex-shrink-0 min-h-[200px]">
                        <div class="w-10 h-10 rounded-xl bg-white border border-gray-200 group-hover:border-brand-300 flex items-center justify-center mb-2 shadow-sm">
                            <svg class="w-5 h-5 text-gray-400 group-hover:text-brand-600 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        </div>
                        <span class="text-xs font-bold uppercase tracking-wider">Add Next Stage</span>
                        <span class="text-[11px] text-gray-400 mt-0.5">Append to workflow</span>
                    </a>
                @endif
            </div>
        </div>
    </div>

    {{-- 2. SORTABLE LIST VIEW --}}
    <div x-show="viewMode === 'list'" x-cloak class="space-y-3">
        <p class="text-xs text-gray-400 italic flex items-center gap-1.5">
            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/></svg>
            Drag rows vertically or use the &uarr; &darr; arrows to reorder.
        </p>

        <div id="sortable-stages-list" class="space-y-2.5">
            @forelse($stages as $index => $stage)
                <div class="list-stage-row bg-white rounded-2xl shadow-sm border border-gray-200 hover:border-brand-400 p-4 flex items-center justify-between gap-4 transition cursor-grab active:cursor-grabbing select-none group"
                     data-id="{{ $stage->id }}"
                     draggable="true"
                     @dragstart="onNativeDragStart($event)"
                     @dragover="onNativeDragOver($event)"
                     @drop="onNativeDrop($event)"
                     @dragend="onNativeDragEnd($event)">
                    
                    <div class="flex items-center gap-3 min-w-0">
                        {{-- Up / Down Buttons + Grip --}}
                        <div class="flex items-center gap-0.5 text-gray-400">
                            <button type="button" @click.stop="moveStage({{ $stage->id }}, 'left')" title="Move Up" class="p-1 hover:text-brand-600 rounded">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                            </button>
                            <button type="button" @click.stop="moveStage({{ $stage->id }}, 'right')" title="Move Down" class="p-1 hover:text-brand-600 rounded">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </button>
                            <div class="p-1 text-gray-300 group-hover:text-gray-500">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"/></svg>
                            </div>
                        </div>

                        <span class="stage-step-badge w-8 h-8 rounded-lg bg-brand-600 text-white font-bold text-xs flex items-center justify-center shadow-sm flex-shrink-0">
                            {{ $index + 1 }}
                        </span>

                        <div class="min-w-0">
                            <h3 class="font-bold text-gray-900 text-sm group-hover:text-brand-600 transition truncate">{{ $stage->name }}</h3>
                            @if($stage->description)
                                <p class="text-xs text-gray-500 truncate max-w-lg">{{ $stage->description }}</p>
                            @endif
                        </div>
                    </div>

                    <div class="flex items-center gap-3 flex-shrink-0">
                        @if($stage->requires_photo)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-50 text-blue-700">📷 Photo</span>
                        @endif
                        @if($stage->requires_pdf)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-purple-50 text-purple-700">📄 PDF</span>
                        @endif
                        <a href="{{ route('admin.stage-products.index', $stage) }}" class="text-xs font-semibold px-2.5 py-1 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700 transition">
                            Materials ({{ $stage->products()->count() }})
                        </a>
                        <a href="{{ route('admin.services.stages.edit', $stage) }}" class="text-xs font-semibold px-2.5 py-1 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700 transition">
                            Edit
                        </a>
                        <form action="{{ route('admin.services.stages.destroy', $stage) }}" method="POST" onsubmit="return confirm('Delete this stage?');" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="p-1 text-gray-400 hover:text-red-600 transition" title="Delete Stage">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 px-6 py-12 text-center text-gray-500">
                    <p class="text-sm">No stages defined yet.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>

<script>
    function stageKanban(serviceId, reorderUrl) {
        return {
            viewMode: 'kanban',
            toast: { show: false, message: '' },
            stageCount: {{ $stages->count() }},
            draggedEl: null,

            init() {
                const self = this;
                // Wait for DOM ready then init SortableJS if available
                setTimeout(() => {
                    self.initSortables();
                }, 100);
            },

            showToast(msg) {
                this.toast.message = msg;
                this.toast.show = true;
                setTimeout(() => { this.toast.show = false; }, 2600);
            },

            initSortables() {
                const self = this;
                if (typeof Sortable === 'undefined') {
                    console.warn('SortableJS not loaded yet, using HTML5 native drag & drop.');
                    return;
                }

                // 1. Kanban Board
                const boardEl = document.getElementById('kanban-stages-board');
                if (boardEl) {
                    new Sortable(boardEl, {
                        animation: 200,
                        draggable: '.kanban-stage-card',
                        filter: 'a, button, form, input',
                        preventOnFilter: false,
                        ghostClass: 'opacity-30',
                        chosenClass: 'ring-2',
                        onEnd: function () {
                            self.syncNumbers();
                            self.sendReorder();
                        }
                    });
                }

                // 2. List View
                const listEl = document.getElementById('sortable-stages-list');
                if (listEl) {
                    new Sortable(listEl, {
                        animation: 200,
                        draggable: '.list-stage-row',
                        filter: 'a, button, form, input',
                        preventOnFilter: false,
                        ghostClass: 'opacity-30',
                        chosenClass: 'ring-2',
                        onEnd: function () {
                            self.syncNumbers();
                            self.sendReorder();
                        }
                    });
                }
            },

            // Native HTML5 Drag and Drop for 100% guarantee on all browsers
            onNativeDragStart(e) {
                this.draggedEl = e.currentTarget;
                e.dataTransfer.effectAllowed = 'move';
                e.dataTransfer.setData('text/plain', this.draggedEl.getAttribute('data-id'));
                this.draggedEl.classList.add('opacity-40');
            },

            onNativeDragOver(e) {
                e.preventDefault();
                e.dataTransfer.dropEffect = 'move';
                const target = e.currentTarget;
                if (!this.draggedEl || target === this.draggedEl) return;

                const parent = target.parentNode;
                const children = Array.from(parent.children);
                const draggedIdx = children.indexOf(this.draggedEl);
                const targetIdx = children.indexOf(target);

                if (draggedIdx < targetIdx) {
                    parent.insertBefore(this.draggedEl, target.nextSibling);
                } else {
                    parent.insertBefore(this.draggedEl, target);
                }
                this.syncNumbers();
            },

            onNativeDrop(e) {
                e.preventDefault();
                if (this.draggedEl) {
                    this.draggedEl.classList.remove('opacity-40');
                    this.draggedEl = null;
                }
                this.syncNumbers();
                this.sendReorder();
            },

            onNativeDragEnd(e) {
                if (this.draggedEl) {
                    this.draggedEl.classList.remove('opacity-40');
                    this.draggedEl = null;
                }
                this.syncNumbers();
            },

            // 1-Click Move Left/Right or Up/Down
            moveStage(stageId, direction) {
                const selector = this.viewMode === 'kanban' ? '.kanban-stage-card' : '.list-stage-row';
                const containerId = this.viewMode === 'kanban' ? 'kanban-stages-board' : 'sortable-stages-list';
                const container = document.getElementById(containerId);
                if (!container) return;

                const items = Array.from(container.querySelectorAll(selector));
                const item = items.find(el => el.getAttribute('data-id') == stageId);
                if (!item) return;

                const idx = items.indexOf(item);
                if (direction === 'left' && idx > 0) {
                    // Move earlier
                    container.insertBefore(item, items[idx - 1]);
                } else if (direction === 'right' && idx < items.length - 1) {
                    // Move later
                    container.insertBefore(item, items[idx + 1].nextSibling);
                }

                this.syncNumbers();
                this.sendReorder();
            },

            syncNumbers() {
                ['#kanban-stages-board .kanban-stage-card', '#sortable-stages-list .list-stage-row'].forEach(selector => {
                    const cards = document.querySelectorAll(selector);
                    cards.forEach((card, idx) => {
                        const badge = card.querySelector('.stage-step-badge');
                        if (badge) badge.textContent = idx + 1;
                        const title = card.querySelector('.stage-step-title');
                        if (title) title.textContent = 'Stage ' + (idx + 1);
                    });
                });
            },

            async sendReorder() {
                const selector = this.viewMode === 'kanban' ? '#kanban-stages-board .kanban-stage-card' : '#sortable-stages-list .list-stage-row';
                const cards = Array.from(document.querySelectorAll(selector));
                const order = cards.map(c => Number(c.getAttribute('data-id'))).filter(Boolean);

                if (order.length === 0) return;

                try {
                    const res = await fetch(reorderUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        },
                        body: JSON.stringify({ order: order })
                    });

                    const data = await res.json();
                    if (data.success) {
                        this.showToast('Workflow stages reordered & saved!');
                    }
                } catch (e) {
                    console.error('Reorder error:', e);
                }
            }
        };
    }
</script>
@endsection
