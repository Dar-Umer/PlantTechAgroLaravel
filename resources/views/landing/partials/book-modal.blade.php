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
        submitted: {{ $formSubmitted ? 'true' : 'false' }},
        loadedAt: Math.floor(Date.now() / 1000)
     }"
     @open-book-modal.window="
        open = true;
        submitted = false;
        loadedAt = Math.floor(Date.now() / 1000);
        if ($event.detail && $event.detail.service) service = $event.detail.service;
     "
     @keydown.escape.window="open = false"
     x-cloak>

    {{-- Backdrop --}}
    <div x-show="open" class="fixed inset-0 z-50 bg-gray-950/50" x-transition.opacity @click="open = false"></div>

    {{-- Modal --}}
    <div x-show="open" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div class="min-h-full flex items-end sm:items-center justify-center p-4">
            <div class="relative w-full max-w-md bg-white rounded-2xl shadow-xl overflow-hidden"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 translate-y-6 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100">

                {{-- Slim accent + minimal header --}}
                <div class="h-1 bg-gradient-to-r from-brand-500 to-emerald-400"></div>
                <div class="px-5 sm:px-6 pt-5 pb-1 flex items-start justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-bold text-gray-900" x-show="!submitted">{{ $leadFormHeading }}</h2>
                        <h2 class="text-lg font-bold text-gray-900" x-show="submitted" x-cloak>Request Received!</h2>
                        <p class="mt-1 text-xs text-gray-500" x-show="!submitted" x-cloak>{{ $leadFormDescription }}</p>
                    </div>
                    <button type="button" @click="open = false; submitted = false" class="text-gray-400 hover:text-gray-600 transition p-1 -mt-1 -mr-1" aria-label="Close">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                {{-- Success Panel --}}
                <div x-show="submitted" x-cloak class="px-6 py-10 text-center">
                    <div class="w-14 h-14 mx-auto rounded-full bg-brand-50 flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <p class="text-sm text-gray-600 leading-relaxed">{{ $leadFormSuccessMessage }}</p>
                    <button type="button" @click="open = false; submitted = false"
                            class="mt-6 inline-flex items-center px-5 py-2.5 rounded-lg bg-brand-600 text-white text-sm font-semibold hover:bg-brand-700 transition">
                        Done
                    </button>
                </div>

                {{-- Form --}}
                <form x-show="!submitted" action="{{ route('leads.store') }}" method="POST" class="px-5 sm:px-6 py-5 space-y-3.5 max-h-[72vh] overflow-y-auto">
                    @csrf

                    {{-- Honeypot + time-trap (anti-bot) --}}
                    <input type="text" name="website" tabindex="-1" autocomplete="off" class="hidden" aria-hidden="true">
                    <input type="hidden" name="loaded_at" x-model="loadedAt">

                    {{-- Mandatory: Name --}}
                    <div>
                        <div class="relative">
                            <input type="text" name="name" id="lead-name" value="{{ old('name') }}" required placeholder=" " autocomplete="name"
                                   class="peer w-full rounded-lg border border-gray-200 bg-gray-50 px-3.5 pt-5 pb-1.5 text-sm text-gray-900 transition focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                            <label for="lead-name"
                                   class="pointer-events-none absolute left-3.5 top-2 text-xs text-gray-400 transition-all duration-150 peer-focus:text-brand-600 peer-focus:top-2 peer-focus:translate-y-0 peer-focus:text-xs peer-placeholder-shown:top-1/2 peer-placeholder-shown:-translate-y-1/2 peer-placeholder-shown:text-base peer-placeholder-shown:text-gray-500">
                                Name <span class="text-red-500">*</span>
                            </label>
                        </div>
                        @error('name')<p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>@enderror
                    </div>

                    {{-- Mandatory: Phone --}}
                    <div>
                        <div class="relative">
                            <input type="tel" name="phone" id="lead-phone" value="{{ old('phone') }}" required placeholder=" " autocomplete="tel"
                                   inputmode="numeric" pattern="[0-9]*" maxlength="10"
                                   oninput="this.value = this.value.replace(/\D/g, '').slice(0, 10)"
                                   class="peer w-full rounded-lg border border-gray-200 bg-gray-50 px-3.5 pt-5 pb-1.5 text-sm text-gray-900 transition focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                            <label for="lead-phone"
                                   class="pointer-events-none absolute left-3.5 top-2 text-xs text-gray-400 transition-all duration-150 peer-focus:text-brand-600 peer-focus:top-2 peer-focus:translate-y-0 peer-focus:text-xs peer-placeholder-shown:top-1/2 peer-placeholder-shown:-translate-y-1/2 peer-placeholder-shown:text-base peer-placeholder-shown:text-gray-500">
                                Phone Number <span class="text-red-500">*</span>
                            </label>
                        </div>
                        @error('phone')<p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>@enderror
                    </div>

                    {{-- Mandatory: Service --}}
                    <div>
                        <div class="relative">
                            <select name="service_id" id="lead-service" x-model="service" required
                                    class="peer w-full rounded-lg border border-gray-200 bg-gray-50 px-3.5 pt-5 pb-1.5 text-sm text-gray-900 transition focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                                <option value="">Select a service</option>
                                @foreach($services as $serviceOption)
                                    <option value="{{ $serviceOption->id }}">{{ $serviceOption->name }}</option>
                                @endforeach
                            </select>
                            <label for="lead-service"
                                   class="pointer-events-none absolute left-3.5 top-2 text-xs text-gray-400 transition-colors duration-150 peer-focus:text-brand-600">
                                Service <span class="text-red-500">*</span>
                            </label>
                        </div>
                        @error('service_id')<p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>@enderror
                    </div>

                    {{-- Dynamic custom fields --}}
                    @include('landing.partials.lead-fields')

                    <div class="pt-1">
                        <button type="submit"
                                class="w-full inline-flex items-center justify-center px-6 py-3 rounded-lg bg-brand-600 text-white text-sm font-bold hover:bg-brand-700 transition shadow-sm shadow-brand-600/25">
                            {{ $leadFormButtonText }}
                        </button>
                        <p class="mt-3 text-[11px] text-center text-gray-400">Our team will call you back to confirm the details.</p>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>