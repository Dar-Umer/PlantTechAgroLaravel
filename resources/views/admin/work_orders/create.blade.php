@extends('admin.layout')

@section('page-title', 'New Work Order')

@section('content')
    <div class="space-y-6 max-w-3xl">
        <div class="flex items-center justify-between">
            <h2 class="text-2xl font-bold text-gray-900">New Work Order</h2>
            <x-admin.button href="{{ route('admin.work-orders.index') }}" variant="secondary" icon='<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>'>Back</x-admin.button>
        </div>

        <div class="bg-brand-50 border border-brand-100 rounded-2xl p-4 text-sm text-brand-800">
            Stages and their default materials are copied from the selected service's templates. Material usage deducts stock as stages are executed.
        </div>

        <form action="{{ route('admin.work-orders.store') }}" method="POST" class="space-y-6"
              x-data="{
                  services: @js($services->map(fn ($s) => ['id' => $s->id, 'name' => $s->name, 'stages' => $s->stages->map(fn ($st) => ['name' => $st->name, 'materials' => $st->products->count()]) ])->values()),
                  preview: null,
                  current() { return this.services.find(s => s.id == this.preview) || null; }
              }">
            @csrf
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <div class="space-y-5">
                    <x-admin.select name="customer_id" label="Customer" :options="$customers->pluck('name', 'id')->all()" :value="old('customer_id')" placeholder="Select a customer" required />
                    <div>
                        <label for="service_id" class="block text-sm font-medium text-gray-700 mb-1.5">Service <span class="text-red-500">*</span></label>
                        <select name="service_id" id="service_id" x-model="preview" required
                                class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 transition focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                            <option value="">Select a service</option>
                            @foreach($services as $service)
                                <option value="{{ $service->id }}">{{ $service->name }} ({{ $service->stages->count() }} stages)</option>
                            @endforeach
                        </select>
                        @error('service_id')
                            <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                        @enderror

                        <template x-if="current()">
                            <div class="mt-3 rounded-xl bg-gray-50 border border-gray-100 p-4 space-y-2">
                                <template x-for="(stage, i) in current().stages" :key="i">
                                    <p class="text-xs text-gray-600">
                                        <span class="inline-flex w-5 h-5 mr-1.5 rounded-full bg-brand-600 text-white items-center justify-center font-bold" x-text="i + 1"></span>
                                        <span x-text="stage.name"></span>
                                        <span class="text-gray-400" x-text="stage.materials > 0 ? '· ' + stage.materials + ' material(s)' : ''"></span>
                                    </p>
                                </template>
                                <p class="text-xs text-gray-400" x-show="current().stages.length === 0">This service has no stages yet.</p>
                            </div>
                        </template>
                    </div>
                    <x-admin.select name="assigned_agent_id" label="Assign Field Agent" :options="$agents->pluck('name', 'id')->all()" :value="old('assigned_agent_id')" placeholder="Assign later" helptext="The agent gets an in-app notification." />
                    <x-admin.textarea name="notes" label="Notes" :value="old('notes')" rows="3" />
                </div>
            </div>

            <div class="flex justify-end">
                <x-admin.button type="submit">Create Work Order</x-admin.button>
            </div>
        </form>
    </div>
@endsection
