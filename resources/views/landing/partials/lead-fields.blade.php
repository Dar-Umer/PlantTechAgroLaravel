{{-- Dynamic custom lead-form fields (used by the Book Now modal) --}}
@foreach($leadFormFields as $field)
    <div>
        <div class="relative">
            @if($field->type === 'textarea')
                <textarea name="custom[{{ $field->name }}]" id="custom-{{ $field->name }}" rows="3" {{ $field->is_required ? 'required' : '' }}
                          class="peer w-full rounded-lg border border-gray-200 bg-gray-50 px-3.5 pt-6 pb-2 text-sm text-gray-900 transition focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">{{ old('custom.' . $field->name) }}</textarea>
                <label for="custom-{{ $field->name }}"
                       class="pointer-events-none absolute left-3.5 top-2.5 text-xs text-gray-400 transition-colors duration-150 peer-focus:text-brand-600">
                    {{ $field->label }}
                    @if($field->is_required) <span class="text-red-500">*</span> @endif
                </label>
            @elseif($field->type === 'select')
                <select name="custom[{{ $field->name }}]" id="custom-{{ $field->name }}" {{ $field->is_required ? 'required' : '' }}
                        class="peer w-full rounded-lg border border-gray-200 bg-gray-50 px-3.5 pt-5 pb-1.5 text-sm text-gray-900 transition focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                    <option value="">Select {{ strtolower($field->label) }}</option>
                    @foreach($field->options ?? [] as $option)
                        <option value="{{ $option }}" {{ old('custom.' . $field->name) === $option ? 'selected' : '' }}>{{ $option }}</option>
                    @endforeach
                </select>
                <label for="custom-{{ $field->name }}"
                       class="pointer-events-none absolute left-3.5 top-2 text-xs text-gray-400 transition-colors duration-150 peer-focus:text-brand-600">
                    {{ $field->label }}
                    @if($field->is_required) <span class="text-red-500">*</span> @endif
                </label>
            @elseif($field->type === 'date')
                <input type="date"
                       name="custom[{{ $field->name }}]"
                       id="custom-{{ $field->name }}"
                       value="{{ old('custom.' . $field->name) }}"
                       {{ $field->is_required ? 'required' : '' }}
                       class="peer w-full rounded-lg border border-gray-200 bg-gray-50 px-3.5 pt-5 pb-1.5 text-sm text-gray-900 transition focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                <label for="custom-{{ $field->name }}"
                       class="pointer-events-none absolute left-3.5 top-2 text-xs text-gray-400 transition-colors duration-150 peer-focus:text-brand-600">
                    {{ $field->label }}
                    @if($field->is_required) <span class="text-red-500">*</span> @endif
                </label>
            @else
                <input type="{{ $field->type }}"
                       name="custom[{{ $field->name }}]"
                       id="custom-{{ $field->name }}"
                       value="{{ old('custom.' . $field->name) }}"
                       placeholder=" "
                       {{ $field->is_required ? 'required' : '' }}
                       class="peer w-full rounded-lg border border-gray-200 bg-gray-50 px-3.5 pt-5 pb-1.5 text-sm text-gray-900 transition focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                <label for="custom-{{ $field->name }}"
                       class="pointer-events-none absolute left-3.5 top-2 text-xs text-gray-400 transition-all duration-150 peer-focus:text-brand-600 peer-focus:top-2 peer-focus:translate-y-0 peer-focus:text-xs peer-placeholder-shown:top-1/2 peer-placeholder-shown:-translate-y-1/2 peer-placeholder-shown:text-base peer-placeholder-shown:text-gray-500">
                    {{ $field->label }}
                    @if($field->is_required) <span class="text-red-500">*</span> @endif
                </label>
            @endif
        </div>
        @error('custom.' . $field->name)
            <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
        @enderror
    </div>
@endforeach