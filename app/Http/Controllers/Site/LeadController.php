<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\LeadFormField;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LeadController extends Controller
{
    public function store(Request $request)
    {
        // Honeypot: bots fill hidden fields — pretend success without saving.
        if ($request->filled('website')) {
            return redirect()->to('/?submitted=1');
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20', 'regex:/^[0-9+\-\s()]{7,20}$/'],
            'service_id' => ['required', Rule::exists('services', 'id')->where('is_active', true)],
        ], [
            'service_id.required' => 'Please select a service.',
            'phone.regex' => 'Please enter a valid phone number.',
        ]);

        $custom = $this->validateCustomFields($request);

        Lead::create([
            'name' => $data['name'],
            'phone' => $data['phone'],
            'service_id' => $data['service_id'],
            'custom_fields' => $custom,
            'status' => 'new',
            'source' => 'landing',
        ]);

        return redirect()->to('/?submitted=1');
    }

    private function validateCustomFields(Request $request): array
    {
        $fields = LeadFormField::active()->get();

        if ($fields->isEmpty()) {
            return [];
        }

        $rules = [];
        $messages = [];

        foreach ($fields as $field) {
            $rule = [];

            if ($field->is_required) {
                $rule[] = 'required';
            } else {
                $rule[] = 'nullable';
            }

            $rule = array_merge($rule, match ($field->type) {
                'email' => ['email', 'max:255'],
                'number' => ['numeric'],
                'date' => ['date'],
                'tel' => ['string', 'max:20', 'regex:/^[0-9+\-\s()]{7,20}$/'],
                'textarea' => ['string', 'max:2000'],
                'select' => [Rule::in($field->options ?? [])],
                default => ['string', 'max:500'],
            });

            $rules["custom.{$field->name}"] = $rule;
            $messages["custom.{$field->name}.required"] = "The {$field->label} field is required.";
            $messages["custom.{$field->name}.in"] = "Please select a valid option for {$field->label}.";
        }

        $validated = $request->validate($rules, $messages);

        return collect($validated['custom'] ?? [])
            ->filter(fn ($value) => $value !== null && $value !== '')
            ->all();
    }
}
