@extends('admin.layout')

@section('page-title', 'New Work Order')

@section('content')
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">New Work Order</h2>
                <p class="text-sm text-gray-500 mt-1">Create a work order by selecting a customer and service. Stages and materials are auto-populated from the service template.</p>
            </div>
            <x-admin.button href="{{ route('admin.work-orders.index') }}" variant="secondary" icon='<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>'>Back</x-admin.button>
        </div>

        <form action="{{ route('admin.work-orders.store') }}" method="POST" class="space-y-6"
              x-data="{
                  services: @js($services->map(fn ($s) => [
                      'id' => $s->id,
                      'name' => $s->name,
                      'stages' => $s->stages->sortBy('sort_order')->map(fn ($st) => [
                          'name' => $st->name,
                          'description' => $st->description,
                          'materials' => $st->products->count(),
                          'requires_photo' => $st->requires_photo,
                          'requires_pdf' => $st->requires_pdf,
                      ])->values(),
                      'total_materials' => $s->stages->sum(fn ($st) => $st->products->count()),
                  ])->values()),
                  preview: null,
                  current() { return this.services.find(s => s.id == this.preview) || null; }
              }">
            @csrf

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {{-- Main Form --}}
                <div class="lg:col-span-2 space-y-6">
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-1">Order Details</h3>
                        <p class="text-sm text-gray-500 mb-4">Select the customer and service for this job.</p>
                        <div class="space-y-5">
                            <x-admin.select name="customer_id" label="Customer" :options="$customers->mapWithKeys(fn ($c) => [$c->id => $c->name . ' — ' . $c->phone])->all()" :value="old('customer_id')" placeholder="Select a customer" required />

                            <div>
                                <label for="service_id" class="block text-sm font-medium text-gray-700 mb-1.5">Service <span class="text-red-500">*</span></label>
                                <select name="service_id" id="service_id" x-model="preview" required
                                        class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 transition focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                                    <option value="">Select a service</option>
                                    @foreach($services as $service)
                                        <option value="{{ $service->id }}">{{ $service->name }} ({{ $service->stages->count() }} stages, {{ $service->stages->sum(fn ($s) => $s->products->count()) }} materials)</option>
                                    @endforeach
                                </select>
                                @error('service_id')
                                    <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                                @enderror
                            </div>

                            <x-admin.textarea name="notes" label="Notes (optional)" :value="old('notes')" rows="3" placeholder="Any special instructions or context for the field agent..." />
                        </div>
                    </div>

                    {{-- Service Stage Preview --}}
                    <template x-if="current()">
                        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-semibold text-gray-900">Service Stages</h3>
                                <span class="text-xs text-gray-500" x-text="current().stages.length + ' stage(s)'"></span>
                            </div>

                            <div x-show="current().stages.length > 0" class="space-y-0">
                                <template x-for="(stage, i) in current().stages" :key="i">
                                    <div class="flex gap-3">
                                        {{-- Step line --}}
                                        <div class="flex flex-col items-center">
                                            <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold flex-shrink-0"
                                                 :class="i === current().stages.length - 1 ? 'bg-brand-600 text-white' : 'bg-brand-100 text-brand-700'"
                                                 x-text="i + 1"></div>
                                            <div x-show="i < current().stages.length - 1" class="w-0.5 flex-1 bg-brand-200 my-1"></div>
                                        </div>
                                        {{-- Content --}}
                                        <div class="pb-5 flex-1 min-w-0">
                                            <p class="text-sm font-semibold text-gray-900" x-text="stage.name"></p>
                                            <p x-show="stage.description" class="text-xs text-gray-500 mt-0.5" x-text="stage.description"></p>
                                            <div class="flex flex-wrap gap-1.5 mt-1.5">
                                                <template x-if="stage.materials > 0">
                                                    <span class="inline-flex items-center gap-1 text-[11px] px-2 py-0.5 rounded-full bg-gray-100 text-gray-600">
                                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                                        <span x-text="stage.materials + ' material(s)'"></span>
                                                    </span>
                                                </template>
                                                <template x-if="stage.requires_photo">
                                                    <span class="inline-flex items-center gap-1 text-[11px] px-2 py-0.5 rounded-full bg-blue-50 text-blue-700">Photo req.</span>
                                                </template>
                                                <template x-if="stage.requires_pdf">
                                                    <span class="inline-flex items-center gap-1 text-[11px] px-2 py-0.5 rounded-full bg-purple-50 text-purple-700">PDF req.</span>
                                                </template>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>

                            <div x-show="current().stages.length === 0" class="text-center py-6 text-sm text-gray-400">
                                This service has no stages configured yet.
                            </div>

                            <div class="mt-4 pt-4 border-t border-gray-100 flex items-center justify-between text-xs text-gray-500">
                                <span x-text="current().total_materials + ' total material(s) will be pre-allocated from stock'"></span>
                            </div>
                        </div>
                    </template>
                </div>

                {{-- Sidebar --}}
                <div class="space-y-6">
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-1">Assign Agent</h3>
                        <p class="text-sm text-gray-500 mb-4">Optional — the assigned agent will receive an in-app notification.</p>
                        <x-admin.select name="assigned_agent_id" label="" :options="$agents->pluck('name', 'id')->all()" :value="old('assigned_agent_id')" placeholder="Assign later" />
                    </div>

                    <div class="bg-brand-50 border border-brand-100 rounded-2xl p-5">
                        <h4 class="text-sm font-semibold text-brand-800 mb-2">How it works</h4>
                        <ol class="text-xs text-brand-700 space-y-1.5 list-decimal list-inside">
                            <li>Stages and materials are copied from the service template.</li>
                            <li>Planned materials are deducted from stock immediately.</li>
                            <li>Agent completes stages and records actual material usage.</li>
                            <li>Once all stages are done, generate an invoice.</li>
                        </ol>
                    </div>

                    <div class="flex justify-end">
                        <x-admin.button type="submit" class="w-full">Create Work Order</x-admin.button>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection
