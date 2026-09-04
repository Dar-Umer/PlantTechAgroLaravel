@php
    $leadFormHeading = config('frontend.lead_form.heading', 'Book Your Service');
    $leadFormDescription = config('frontend.lead_form.description');
    $leadFormButtonText = config('frontend.lead_form.button_text', 'Submit Request');
    $leadFormSuccessMessage = config('frontend.lead_form.success_message', 'Thank you! Our team will contact you soon.');
    $services = $services ?? collect();
    $leadFormFields = $leadFormFields ?? collect();
    $formSubmitted = request('submitted') === '1';
    $formErrored = $errors->any() && (old('name') || old('phone') || old('service_id') || old()->hasAny(array_map(fn ($f) => 'custom.' . $f->name, $leadFormFields->all())));
@endphp

<div x-data="{
        open: {{ $formSubmitted || $formErrored ? 'true' : 'false' }},
        service: '{{ old('service_id') }}',
        submitted: {{ $formSubmitted ? 'true' : 'false' }}
     }"
     @open-book-modal.window="
        open = true;
        submitted = false;
        if ($event.detail && $event.detail.service) service = $event.detail.service;
     "
     @keydown.escape.window="open = false"
     x-cloak>

    {{-- Backdrop --}}
    <div x-show="open" class="fixed inset-0 z-50 bg-gray-950/60 backdrop-blur-sm" x-transition.opacity @click="open = false"></div>

    {{-- Modal --}}
    <div x-show="open" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div class="min-h-full flex items-end sm:items-center justify-center p-4">
            <div class="relative w-full max-w-lg bg-white rounded-3xl shadow-2xl overflow-hidden"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 translate-y-6 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100">

                {{-- Header --}}
                <div class="bg-gradient-to-br from-brand-700 to-brand-800 px-6 sm:px-8 py-7 relative">
                    <button type="button" @click="open = false; submitted = false" class="absolute top-4 right-4 text-white/70 hover:text-white transition p-1">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                    <h2 class="text-xl font-extrabold text-white" x-show="!submitted">{{ $leadFormHeading }}</h2>
                    <h2 class="text-xl font-extrabold text-white" x-show="submitted" x-cloak>Request Received!</h2>
                    <p class="mt-1.5 text-sm text-brand-100" x-show="!submitted">{{ $leadFormDescription }}</p>
                </div>

                {{-- Success Panel --}}
                <div x-show="submitted" x-cloak class="px-6 sm:px-8 py-12 text-center">
                    <div class="w-16 h-16 mx-auto rounded-full bg-brand-100 flex items-center justify-center mb-5">
                        <svg class="w-8 h-8 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <p class="text-gray-700 leading-relaxed">{{ $leadFormSuccessMessage }}</p>
                    <button type="button" @click="open = false; submitted = false"
                            class="mt-8 inline-flex items-center px-6 py-3 rounded-xl bg-brand-600 text-white text-sm font-semibold hover:bg-brand-700 transition">
                        Done
                    </button>
                </div>

                {{-- Form --}}
                <form x-show="!submitted" action="{{ route('leads.store') }}" method="POST" class="px-6 sm:px-8 py-7 space-y-4 max-h-[70vh] overflow-y-auto">
                    @csrf

                    {{-- Honeypot --}}
                    <input type="text" name="website" tabindex="-1" autocomplete="off" class="hidden" aria-hidden="true">

                    {{-- Mandatory: Name --}}
                    <div>
                        <label for="lead-name" class="block text-sm font-medium text-gray-700 mb-1.5">Name <span class="text-red-500">*</span></label>
                        <input type="text" name="name" id="lead-name" value="{{ old('name') }}" required placeholder="Enter your full name"
                               class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 placeholder-gray-400 transition focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                        @error('name')<p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>@enderror
                    </div>

                    {{-- Mandatory: Phone --}}
                    <div>
                        <label for="lead-phone" class="block text-sm font-medium text-gray-700 mb-1.5">Phone Number <span class="text-red-500">*</span></label>
                        <input type="tel" name="phone" id="lead-phone" value="{{ old('phone') }}" required placeholder="Enter your phone number"
                               class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 placeholder-gray-400 transition focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                        @error('phone')<p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>@enderror
                    </div>

                    {{-- Mandatory: Service --}}
                    <div>
                        <label for="lead-service" class="block text-sm font-medium text-gray-700 mb-1.5">Service <span class="text-red-500">*</span></label>
                        <select name="service_id" id="lead-service" x-model="service" required
                                class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 transition focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                            <option value="">Select a service</option>
                            @foreach($services as $serviceOption)
                                <option value="{{ $serviceOption->id }}">{{ $serviceOption->name }}</option>
                            @endforeach
                        </select>
                        @error('service_id')<p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>@enderror
                    </div>

                    {{-- Dynamic custom fields --}}
                    @include('landing.partials.lead-fields')

                    <div class="pt-2">
                        <button type="submit"
                                class="w-full inline-flex items-center justify-center gap-2 px-6 py-3.5 rounded-xl bg-brand-600 text-white font-bold hover:bg-brand-700 transition shadow-lg shadow-brand-600/25">
                            {{ $leadFormButtonText }}
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                        </button>
                        <p class="mt-3 text-xs text-center text-gray-400">Our team will call you back to confirm the details.</p>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
