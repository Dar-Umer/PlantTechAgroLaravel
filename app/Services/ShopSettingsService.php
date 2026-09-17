<?php

namespace App\Services;

use App\Models\Setting;
use App\Support\ContentCache;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class ShopSettingsService
{
    protected const CACHE_KEY = 'shop.settings.config';

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

        Cache::forget(self::CACHE_KEY);
        ContentCache::bump();
    }

    public function mergeIntoConfig(): void
    {
        foreach ($this->all() as $key => $value) {
            config([$key => $value]);
        }
    }

    /**
     * All stored settings, keyed by their config path. Cached because this runs
     * on every request during bootstrap.
     *
     * @return array<string, mixed>
     */
    public function all(): array
    {
        try {
            $cached = Cache::get(self::CACHE_KEY);

            if (is_array($cached)) {
                return $cached;
            }

            if (! Schema::hasTable('settings')) {
                // Don't cache the transient pre-migration state.
                return [];
            }

            $settings = Setting::query()->get()
                ->mapWithKeys(fn (Setting $setting) => [$setting->key => $setting->value])
                ->all();

            Cache::forever(self::CACHE_KEY, $settings);

            return $settings;
        } catch (\Throwable) {
            return [];
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
