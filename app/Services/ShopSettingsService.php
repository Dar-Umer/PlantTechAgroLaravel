<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Schema;

class ShopSettingsService
{
    public function set(array $settings, string $namespace = ''): void
    {
        $rows = [];

        foreach ($settings as $key => $value) {
            $fullKey = $namespace !== '' ? $namespace.'.'.$key : $key;
            $value = $this->normalize($value);

            $rows[] = ['key' => $fullKey, 'value' => json_encode($value)];

            config([$fullKey => $value]);
        }

        Setting::upsert($rows, ['key'], ['value']);
    }

    public function mergeIntoConfig(): void
    {
        try {
            if (! Schema::hasTable('settings')) {
                return;
            }
        } catch (\Throwable) {
            return;
        }

        foreach (Setting::query()->get() as $setting) {
            config([$setting->key => $setting->value]);
        }
    }

    private function normalize(mixed $value): mixed
    {
        if (! is_string($value)) {
            return $value;
        }

        if (is_numeric($value)) {
            return $value + 0;
        }

        if ($value === 'true') {
            return true;
        }

        if ($value === 'false') {
            return false;
        }

        return $value;
    }
}
