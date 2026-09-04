{{-- Dynamic custom lead-form fields (used by the Book Now modal) --}}
@foreach($leadFormFields as $field)
    <div>
        <label for="custom-{{ $field->name }}" class="block text-sm font-medium text-gray-700 mb-1.5">
            {{ $field->label }}
            @if($field->is_required) <span class="text-red-500">*</span> @endif
        </label>

        @if($field->type === 'textarea')
            <textarea name="custom[{{ $field->name }}]" id="custom-{{ $field->name }}" rows="3" {{ $field->is_required ? 'required' : '' }}
                      placeholder="Enter {{ strtolower($field->label) }}"
                      class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 placeholder-gray-400 transition focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">{{ old('custom.' . $field->name) }}</textarea>
        @elseif($field->type === 'select')
            <select name="custom[{{ $field->name }}]" id="custom-{{ $field->name }}" {{ $field->is_required ? 'required' : '' }}
                    class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 transition focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                <option value="">Select {{ strtolower($field->label) }}</option>
                @foreach($field->options ?? [] as $option)
                    <option value="{{ $option }}" {{ old('custom.' . $field->name) === $option ? 'selected' : '' }}>{{ $option }}</option>
                @endforeach
            </select>
        @else
            <input type="{{ $field->type }}"
                   name="custom[{{ $field->name }}]"
                   id="custom-{{ $field->name }}"
                   value="{{ old('custom.' . $field->name) }}"
                   placeholder="Enter {{ strtolower($field->label) }}"
                   {{ $field->is_required ? 'required' : '' }}
                   class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 placeholder-gray-400 transition focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
        @endif

        @error('custom.' . $field->name)
            <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
        @enderror
    </div>
@endforeach
