<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LeadFormField;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class LeadFormFieldController extends Controller
{
    public function store(Request $request)
    {
        $data = $this->validated($request);

        $data['name'] = $this->uniqueName($data['label']);
        $data['options'] = $this->normalizeOptions($data);

        LeadFormField::create($data);

        return redirect()->route('admin.frontend.index', ['tab' => 'lead_form'])
            ->with('success', 'Form field added.');
    }

    public function update(Request $request, LeadFormField $field)
    {
        $data = $this->validated($request);
        $data['options'] = $this->normalizeOptions($data);

        $field->update($data);

        return redirect()->route('admin.frontend.index', ['tab' => 'lead_form'])
            ->with('success', 'Form field updated.');
    }

    public function destroy(LeadFormField $field)
    {
        $field->delete();

        return redirect()->route('admin.frontend.index', ['tab' => 'lead_form'])
            ->with('success', 'Form field deleted.');
    }

    public function reorder(Request $request)
    {
        $validated = $request->validate([
            'orders' => ['required', 'array'],
            'orders.*' => ['required', 'integer', 'min:0'],
        ]);

        foreach ($validated['orders'] as $fieldId => $sortOrder) {
            LeadFormField::whereKey($fieldId)->update(['sort_order' => $sortOrder]);
        }

        return redirect()->route('admin.frontend.index', ['tab' => 'lead_form'])
            ->with('success', 'Field order saved.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'label' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in(array_keys(LeadFormField::TYPES))],
            'options' => ['nullable', 'string'],
            'is_required' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);
    }

    private function normalizeOptions(array $data): ?array
    {
        if ($data['type'] !== 'select') {
            return null;
        }

        $options = collect(explode(',', $data['options'] ?? ''))
            ->map(fn ($option) => trim($option))
            ->filter()
            ->values()
            ->all();

        if (empty($options)) {
            throw ValidationException::withMessages([
                'options' => 'Please provide at least one dropdown option (comma-separated).',
            ]);
        }

        return $options;
    }

    private function uniqueName(string $label): string
    {
        $base = Str::slug($label, '_') ?: 'field';
        $name = $base;
        $attempt = 1;

        while (LeadFormField::where('name', $name)->exists()) {
            $attempt++;
            $name = $base.'_'.$attempt;
        }

        return $name;
    }
}
