<?php

namespace App\Support;

use Illuminate\Support\Facades\Http;

/**
 * Google reCAPTCHA v3 verification.
 *
 * Fail-closed: any verification failure (network error, bad response,
 * low score) returns false and the caller must reject the submission.
 * When the integration is disabled or keys are blank, callers skip
 * verification entirely (see enabled()).
 */
class Recaptcha
{
    public static function enabled(): bool
    {
        return (bool) config('apis.recaptcha_enabled', false)
            && filled(config('apis.recaptcha_site_key'))
            && filled(config('apis.recaptcha_secret_key'));
    }

    public static function siteKey(): string
    {
        return (string) config('apis.recaptcha_site_key', '');
    }

    /**
     * @param  string|null  $expectedAction  Binds the token to the form that
     *     requested it (e.g. 'lead', 'admin_login'). Tokens minted for another
     *     action — or on another domain using our public site key — are rejected.
     */
    public static function verify(?string $token, ?string $ip = null, ?string $expectedAction = null): bool
    {
        if ($token === null || $token === '') {
            return false;
        }

        try {
            $response = Http::timeout(5)
                ->asForm()
                ->post('https://www.google.com/recaptcha/api/siteverify', array_filter([
                    'secret' => config('apis.recaptcha_secret_key'),
                    'response' => $token,
                    'remoteip' => $ip,
                ]));

            if (! $response->successful()) {
                return false;
            }

            $json = $response->json();

            if (($json['success'] ?? false) !== true) {
                return false;
            }

            if ((float) ($json['score'] ?? 0) < (float) config('apis.recaptcha_min_score', 0.5)) {
                return false;
            }

            if ($expectedAction !== null && ($json['action'] ?? null) !== $expectedAction) {
                return false;
            }

            $expectedHost = strtolower((string) parse_url((string) config('app.url'), PHP_URL_HOST));
            $actualHost = strtolower((string) ($json['hostname'] ?? ''));

            if ($expectedHost !== '' && $actualHost !== '' && $actualHost !== $expectedHost) {
                return false;
            }

            return true;
        } catch (\Throwable) {
            return false;
        }
    }
}
