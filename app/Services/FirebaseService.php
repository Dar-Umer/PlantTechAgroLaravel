<?php

namespace App\Services;

use App\Models\DeviceToken;
use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FirebaseService
{
    private const OAUTH_URL = 'https://oauth2.googleapis.com/token';
    private const SCOPE = 'https://www.googleapis.com/auth/firebase.messaging';

    /**
     * Determine if Firebase notifications are enabled and configured.
     */
    public function isEnabled(): bool
    {
        $enabled = config('services.firebase.enabled', true);
        if (is_bool($enabled)) {
            $isEnabled = $enabled;
        } else {
            $isEnabled = filter_var($enabled, FILTER_VALIDATE_BOOLEAN);
        }

        return $isEnabled && $this->getCredentials() !== null;
    }

    /**
     * Retrieve the Firebase service account credentials.
     * Looks first in database settings, then raw JSON env, then service-account.json file.
     */
    public function getCredentials(): ?array
    {
        // 1. From database settings
        $dbConfig = Setting::get('firebase_service_account');
        if (! empty($dbConfig)) {
            $decoded = is_array($dbConfig) ? $dbConfig : json_decode((string) $dbConfig, true);
            if (is_array($decoded) && ! empty($decoded['project_id']) && ! empty($decoded['private_key'])) {
                return $decoded;
            }
        }

        // 2. From raw JSON in env / config
        $jsonEnv = config('services.firebase.credentials_json');
        if (! empty($jsonEnv)) {
            $decoded = json_decode((string) $jsonEnv, true);
            if (is_array($decoded) && ! empty($decoded['project_id']) && ! empty($decoded['private_key'])) {
                return $decoded;
            }
        }

        // 3. From JSON file path
        $filePath = config('services.firebase.credentials_file');
        if ($filePath && file_exists($filePath)) {
            $content = file_get_contents($filePath);
            $decoded = json_decode((string) $content, true);
            if (is_array($decoded) && ! empty($decoded['project_id']) && ! empty($decoded['private_key'])) {
                return $decoded;
            }
        }

        return null;
    }

    /**
     * Get or generate a Google OAuth2 Access Token for HTTP v1 FCM API.
     */
    public function getAccessToken(): ?string
    {
        $credentials = $this->getCredentials();
        if (! $credentials) {
            return null;
        }

        $cacheKey = 'firebase_fcm_access_token_' . md5($credentials['client_email'] ?? 'default');

        return Cache::remember($cacheKey, now()->addMinutes(50), function () use ($credentials) {
            return $this->requestOAuth2Token($credentials);
        });
    }

    /**
     * Perform RS256 JWT signing and exchange with Google OAuth2 server.
     */
    protected function requestOAuth2Token(array $credentials): ?string
    {
        $now = time();
        $clientEmail = $credentials['client_email'] ?? '';
        $privateKey = $credentials['private_key'] ?? '';
        $tokenUri = $credentials['token_uri'] ?? self::OAUTH_URL;

        if (empty($clientEmail) || empty($privateKey)) {
            Log::warning('FirebaseService: Missing client_email or private_key.');
            return null;
        }

        $header = ['alg' => 'RS256', 'typ' => 'JWT'];
        $claims = [
            'iss' => $clientEmail,
            'scope' => self::SCOPE,
            'aud' => $tokenUri,
            'iat' => $now,
            'exp' => $now + 3600,
        ];

        $base64Header = $this->base64UrlEncode(json_encode($header));
        $base64Claims = $this->base64UrlEncode(json_encode($claims));
        $signingInput = "{$base64Header}.{$base64Claims}";

        $signature = '';
        $success = openssl_sign($signingInput, $signature, $privateKey, OPENSSL_ALGO_SHA256);

        if (! $success) {
            Log::error('FirebaseService: OpenSSL failed to sign Google JWT with private key.');
            return null;
        }

        $jwt = "{$signingInput}." . $this->base64UrlEncode($signature);

        $response = Http::asForm()->timeout(15)->post($tokenUri, [
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion' => $jwt,
        ]);

        if (! $response->successful()) {
            Log::error('FirebaseService: Failed to retrieve Google OAuth2 access token: ' . $response->body());
            return null;
        }

        $data = $response->json();
        return $data['access_token'] ?? null;
    }

    /**
     * Send push notification to a single device token using HTTP v1 API.
     */
    public function sendToToken(string $token, string $title, string $body, array $data = [], ?string $imageUrl = null): array
    {
        return $this->sendFCMMessage(['token' => $token], $title, $body, $data, $imageUrl);
    }

    /**
     * Send push notification to a topic (e.g. 'farmers', 'announcements').
     */
    public function sendToTopic(string $topic, string $title, string $body, array $data = [], ?string $imageUrl = null): array
    {
        // Strip /topics/ prefix if present
        $cleanTopic = preg_replace('/^\/?topics\//', '', $topic);
        return $this->sendFCMMessage(['topic' => $cleanTopic], $title, $body, $data, $imageUrl);
    }

    /**
     * Send push notification to multiple device tokens.
     */
    public function sendToTokens(array $tokens, string $title, string $body, array $data = [], ?string $imageUrl = null): array
    {
        $results = [
            'success_count' => 0,
            'failure_count' => 0,
            'total' => count($tokens),
            'responses' => [],
        ];

        foreach (array_unique(array_filter($tokens)) as $token) {
            $res = $this->sendToToken($token, $title, $body, $data, $imageUrl);
            $results['responses'][] = $res;
            if ($res['success']) {
                $results['success_count']++;
            } else {
                $results['failure_count']++;
            }
        }

        return $results;
    }

    /**
     * Dispatch message to Firebase HTTP v1 endpoint.
     */
    protected function sendFCMMessage(array $target, string $title, string $body, array $data = [], ?string $imageUrl = null): array
    {
        $credentials = $this->getCredentials();
        if (! $credentials) {
            return [
                'success' => false,
                'message' => 'Firebase credentials not found or configured.',
            ];
        }

        $projectId = $credentials['project_id'] ?? config('services.firebase.project_id');
        if (empty($projectId)) {
            return [
                'success' => false,
                'message' => 'Firebase project ID missing.',
            ];
        }

        $accessToken = $this->getAccessToken();
        if (! $accessToken) {
            return [
                'success' => false,
                'message' => 'Could not obtain Google OAuth2 access token for Firebase.',
            ];
        }

        // Convert all data values to string as required by FCM
        $stringData = [];
        foreach ($data as $k => $v) {
            if (is_array($v) || is_object($v)) {
                $stringData[(string) $k] = json_encode($v);
            } else {
                $stringData[(string) $k] = (string) $v;
            }
        }

        $notificationPayload = [
            'title' => $title,
            'body' => $body,
        ];
        if (! empty($imageUrl)) {
            $notificationPayload['image'] = $imageUrl;
        }

        $message = array_merge($target, [
            'notification' => $notificationPayload,
            'data' => $stringData,
            'android' => [
                'priority' => 'HIGH',
                'notification' => [
                    'sound' => 'default',
                    'channel_id' => 'pta_notifications',
                    'default_sound' => true,
                    'default_vibrate_timings' => true,
                    'notification_priority' => 'PRIORITY_MAX',
                    'visibility' => 'PUBLIC',
                ],
            ],
            'apns' => [
                'headers' => [
                    'apns-priority' => '10',
                    'apns-push-type' => 'alert',
                ],
                'payload' => [
                    'aps' => [
                        'alert' => [
                            'title' => $title,
                            'body' => $body,
                        ],
                        'sound' => 'default',
                        'badge' => 1,
                        'content-available' => 1,
                    ],
                ],
            ],
        ]);

        $url = "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send";

        try {
            $response = Http::withToken($accessToken)
                ->withHeaders(['Content-Type' => 'application/json; UTF-8'])
                ->timeout(15)
                ->post($url, ['message' => $message]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'message_id' => $response->json('name'),
                    'target' => $target,
                ];
            }

            $errorBody = $response->json();
            $status = $errorBody['error']['status'] ?? '';
            $errorMessage = $errorBody['error']['message'] ?? $response->body();

            // Auto-clean expired / invalid tokens
            if (isset($target['token']) && in_array($status, ['UNREGISTERED', 'INVALID_ARGUMENT', 'NOT_FOUND'], true)) {
                DeviceToken::where('token', $target['token'])->delete();
                Log::info("FirebaseService: Cleaned invalid device token {$target['token']}");
            }

            Log::warning("FirebaseService: FCM error ({$response->status()}): {$errorMessage}");

            return [
                'success' => false,
                'status' => $response->status(),
                'error' => $errorMessage,
                'target' => $target,
            ];
        } catch (\Throwable $e) {
            Log::error('FirebaseService: Request exception: ' . $e->getMessage());

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'target' => $target,
            ];
        }
    }

    /**
     * Test connection and credentials with Google.
     */
    public function testConnection(?string $testToken = null): array
    {
        $credentials = $this->getCredentials();
        if (! $credentials) {
            return [
                'success' => false,
                'message' => 'No Firebase credentials configured. Please provide a Service Account JSON.',
            ];
        }

        $token = $this->getAccessToken();
        if (! $token) {
            return [
                'success' => false,
                'message' => 'Failed to generate OAuth2 Access Token with Google. Verify your client_email and private_key.',
            ];
        }

        if (! empty($testToken)) {
            return $this->sendToToken(
                $testToken,
                'PTA Test Notification',
                'Your Firebase Cloud Messaging push notification is successfully configured and working!',
                ['type' => 'test', 'timestamp' => (string) now()->timestamp]
            );
        }

        return [
            'success' => true,
            'message' => 'Credentials verified! Google OAuth2 access token retrieved successfully for project ' . ($credentials['project_id'] ?? 'unknown') . '.',
            'project_id' => $credentials['project_id'] ?? null,
            'client_email' => $credentials['client_email'] ?? null,
        ];
    }

    private function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
