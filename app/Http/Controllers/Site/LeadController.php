<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\LeadFormField;
use App\Notifications\NewLeadAlert;
use App\Services\AdminNotifier;
use App\Support\Recaptcha;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

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
            'phone' => ['required', 'string', 'max:10', 'regex:/^[0-9]{10}$/'],
            'service_id' => ['required', Rule::exists('services', 'id')->where('is_active', true)],
        ], [
            'service_id.required' => 'Please select a service.',
            'phone.regex' => 'Please enter a valid 10-digit phone number.',
        ]);

        $custom = $this->validateCustomFields($request);

        // Time trap: a real visitor needs a moment to fill the form. Bots submit instantly.
        $minSeconds = (int) config('frontend.lead_form.min_submit_seconds', 3);
        $loadedAt = (int) $request->input('loaded_at');

        if (! $request->filled('loaded_at') || $loadedAt <= 0 || $loadedAt > time() + 60 || (time() - $loadedAt) < $minSeconds) {
            return redirect()->to('/?submitted=1');
        }

        if (Recaptcha::enabled() && ! Recaptcha::verify($request->input('g-recaptcha-response'), $request->ip())) {
            throw ValidationException::withMessages([
                'g-recaptcha-response' => 'Bot verification failed. Please refresh the page and try again.',
            ]);
        }

        $lead = Lead::create([
            'name' => $data['name'],
            'phone' => $data['phone'],
            'service_id' => $data['service_id'],
            'custom_fields' => $custom,
            'status' => 'new',
            'source' => 'landing',
        ]);

        if (config('automation.new_lead_alerts_enabled', true)) {
            AdminNotifier::send(new NewLeadAlert($lead));
        }

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
                'tel' => ['string', 'max:10', 'regex:/^[0-9]{10}$/'],
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
