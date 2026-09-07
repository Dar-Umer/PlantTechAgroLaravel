<?php

namespace App\Services;

class MailSettingsService
{
    /** Maps the stored `mail.*` settings keys onto the resolved config paths. */
    protected function mapping(): array
    {
        return [
            'default' => 'mail.default',
            'smtp_host' => 'mail.mailers.smtp.host',
            'smtp_port' => 'mail.mailers.smtp.port',
            'smtp_username' => 'mail.mailers.smtp.username',
            'smtp_password' => 'mail.mailers.smtp.password',
            'smtp_encryption' => 'mail.mailers.smtp.encryption',
            'from_address' => 'mail.from.address',
            'from_name' => 'mail.from.name',
        ];
    }

    /**
     * Apply the values currently stored in the settings table (loaded into
     * config under `mail.*`) onto the live mail config.
     */
    public function applyFromStored(): void
    {
        $values = [];

        foreach ($this->mapping() as $key => $path) {
            $value = config('mail.'.$key);

            if ($value === null) {
                continue;
            }

            $values[$key] = $value;
        }

        $this->merge($values);
    }

    /** Apply an explicit set of values (e.g. from a test-email form) to config. */
    public function applyFrom(array $values): void
    {
        $values = array_filter($values, fn ($value) => $value !== null && $value !== '');

        $this->merge($values);
    }

    public function merge(array $values): void
    {
        foreach ($this->mapping() as $key => $path) {
            if (array_key_exists($key, $values)) {
                config([$path => $values[$key]]);
            }
        }
    }
}