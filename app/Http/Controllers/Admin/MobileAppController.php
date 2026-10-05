<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\DeviceToken;
use App\Models\Setting;
use App\Services\FirebaseService;
use App\Services\ShopSettingsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Validation\Rule;

class MobileAppController extends Controller
{
    public function __construct(protected FirebaseService $firebase) {}

    public function index()
    {
        $palettes = config('theme.palettes', []);
        $fonts = config('theme.fonts', []);

        $settings = [
            'app_name' => config('mobile.app_name', ''),
            'splash_tagline' => config('mobile.splash_tagline', ''),
            'app_palette' => config('mobile.app_palette', ''),
            'app_font_family' => config('mobile.app_font_family', ''),
            'app_logo_url' => config('mobile.app_logo_url', ''),
            'version' => config('mobile.version', '1.0.0'),
            'build_number' => config('mobile.build_number', '1'),
            'minimum_supported' => config('mobile.minimum_supported', '1.0.0'),
            'force_update' => (bool) config('mobile.force_update', false),
            'android_update_url' => config('mobile.android_update_url', ''),
            'ios_update_url' => config('mobile.ios_update_url', ''),
            'release_notes' => config('mobile.release_notes', ''),
            'maintenance_mode' => (bool) config('mobile.maintenance_mode', false),
            'echo_otp' => (bool) config('mobile.echo_otp', config('api.echo_otp', true)),
            // Firebase Push
            'firebase_enabled' => (bool) config('services.firebase.enabled', true),
            'firebase_project_id' => config('services.firebase.project_id', ''),
        ];

        // Retrieve existing service account metadata if present
        $credentials = $this->firebase->getCredentials();
        $hasServiceAccount = ! empty($credentials['project_id']) && ! empty($credentials['private_key']);
        $serviceAccountClientEmail = $credentials['client_email'] ?? null;
        $firebaseProjectId = $credentials['project_id'] ?? $settings['firebase_project_id'];

        // Registered device statistics
        $totalDevices = DeviceToken::count();
        $androidDevices = DeviceToken::where('platform', 'android')->count();
        $iosDevices = DeviceToken::where('platform', 'ios')->count();
        $recentTokens = DeviceToken::with('tokenable')->latest('last_active_at')->limit(12)->get();
        $customersWithTokens = Customer::whereHas('deviceTokens')->get(['id', 'name', 'phone', 'orchardist_id']);

        return view('admin.mobile-apps.index', compact(
            'settings',
            'palettes',
            'fonts',
            'hasServiceAccount',
            'serviceAccountClientEmail',
            'firebaseProjectId',
            'totalDevices',
            'androidDevices',
            'iosDevices',
            'recentTokens',
            'customersWithTokens'
        ));
    }

    public function update(Request $request)
    {
        $paletteKeys = array_keys(config('theme.palettes', []));
        $fontKeys = array_keys(config('theme.fonts', []));

        $validated = $request->validate([
            'app_name' => ['nullable', 'string', 'max:120'],
            'splash_tagline' => ['nullable', 'string', 'max:200'],
            'app_palette' => ['nullable', Rule::in(array_merge([''], $paletteKeys))],
            'app_font_family' => ['nullable', Rule::in(array_merge([''], $fontKeys))],
            'app_logo_url' => ['nullable', 'string', 'max:500'],
            'version' => ['nullable', 'string', 'max:32', 'regex:/^[0-9]+(\.[0-9]+){0,2}$/'],
            'build_number' => ['nullable', 'string', 'max:32'],
            'minimum_supported' => ['nullable', 'string', 'max:32', 'regex:/^[0-9]+(\.[0-9]+){0,2}$/'],
            'force_update' => ['nullable', 'in:0,1'],
            'android_update_url' => ['nullable', 'url', 'max:500'],
            'ios_update_url' => ['nullable', 'url', 'max:500'],
            'release_notes' => ['nullable', 'string', 'max:2000'],
            'maintenance_mode' => ['nullable', 'in:0,1'],
            'echo_otp' => ['nullable', 'in:0,1'],
            'app_logo_file' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,gif', 'max:2048'],
            // Firebase validation
            'firebase_enabled' => ['nullable', 'in:0,1'],
            'firebase_project_id' => ['nullable', 'string', 'max:100'],
            'firebase_service_account_file' => ['nullable', 'file', 'max:2048'],
            'firebase_service_account_json' => ['nullable', 'string'],
        ]);

        $currentTab = $request->input('tab', 'appearance');

        // Handle general mobile settings
        $settings = [
            'app_name' => $validated['app_name'] ?? config('mobile.app_name', ''),
            'splash_tagline' => $validated['splash_tagline'] ?? config('mobile.splash_tagline', ''),
            'app_palette' => $validated['app_palette'] ?? config('mobile.app_palette', ''),
            'app_font_family' => $validated['app_font_family'] ?? config('mobile.app_font_family', ''),
            'app_logo_url' => config('mobile.app_logo_url', ''),
            'version' => $validated['version'] ?? config('mobile.version', '1.0.0'),
            'build_number' => $validated['build_number'] ?? config('mobile.build_number', '1'),
            'minimum_supported' => $validated['minimum_supported'] ?? config('mobile.minimum_supported', '1.0.0'),
            'force_update' => ($validated['force_update'] ?? '0') === '1',
            'android_update_url' => $validated['android_update_url'] ?? config('mobile.android_update_url', ''),
            'ios_update_url' => $validated['ios_update_url'] ?? config('mobile.ios_update_url', ''),
            'release_notes' => $validated['release_notes'] ?? config('mobile.release_notes', ''),
            'maintenance_mode' => ($validated['maintenance_mode'] ?? '0') === '1',
            'echo_otp' => ($validated['echo_otp'] ?? '0') === '1',
        ];

        if ($request->hasFile('app_logo_file')) {
            $path = $request->file('app_logo_file')->store('logos', 'public');
            $settings['app_logo_url'] = '/storage/'.$path;
        } elseif ($request->input('remove_app_logo') === '1') {
            $settings['app_logo_url'] = '';
        }

        app(ShopSettingsService::class)->set($settings, 'mobile');

        // Synchronize maintenance_mode with site-wide shop settings
        app(ShopSettingsService::class)->set([
            'maintenance_mode' => $settings['maintenance_mode'],
        ], 'shop');

        // Handle Firebase settings if provided or submitted from the push tab
        if ($request->has('firebase_form_submitted') || $currentTab === 'push') {
            $firebaseEnabled = ($request->input('firebase_enabled', '0') === '1');
            $firebaseProjectId = $request->input('firebase_project_id');

            // Check if JSON file was uploaded
            $uploadedJson = null;
            if ($request->hasFile('firebase_service_account_file')) {
                $fileContent = File::get($request->file('firebase_service_account_file')->getRealPath());
                $decoded = json_decode($fileContent, true);
                if (is_array($decoded) && ! empty($decoded['project_id']) && ! empty($decoded['private_key'])) {
                    $uploadedJson = $decoded;
                } else {
                    return redirect()->route('admin.mobile-apps.index', ['tab' => 'push'])
                        ->withErrors(['firebase_service_account_file' => 'The uploaded file is not a valid Firebase Service Account JSON (must contain project_id and private_key).']);
                }
            } elseif (! empty($validated['firebase_service_account_json'])) {
                $decoded = json_decode($validated['firebase_service_account_json'], true);
                if (is_array($decoded) && ! empty($decoded['project_id']) && ! empty($decoded['private_key'])) {
                    $uploadedJson = $decoded;
                } else {
                    return redirect()->route('admin.mobile-apps.index', ['tab' => 'push'])
                        ->withErrors(['firebase_service_account_json' => 'The pasted JSON string is invalid or missing required credentials (project_id, private_key).']);
                }
            }

            if ($uploadedJson) {
                // Save safely to storage/app/firebase/service-account.json
                $dir = storage_path('app/firebase');
                if (! File::isDirectory($dir)) {
                    File::makeDirectory($dir, 0755, true);
                }
                File::put($dir . '/service-account.json', json_encode($uploadedJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

                // Also persist in settings table
                Setting::updateOrCreate(
                    ['key' => 'firebase_service_account'],
                    ['value' => $uploadedJson]
                );

                if (empty($firebaseProjectId)) {
                    $firebaseProjectId = $uploadedJson['project_id'] ?? '';
                }
            }

            app(ShopSettingsService::class)->set([
                'enabled' => $firebaseEnabled,
                'project_id' => $firebaseProjectId ?? '',
            ], 'services.firebase');
        }

        return redirect()->route('admin.mobile-apps.index', ['tab' => $currentTab])
            ->with('success', 'Mobile app settings updated successfully.');
    }

    /**
     * Test Firebase connection and optionally send a test push message.
     */
    public function testPush(Request $request)
    {
        $request->validate([
            'test_target_type' => ['required', 'in:token,customer,connection_only'],
            'test_device_token' => ['nullable', 'string', 'required_if:test_target_type,token'],
            'test_customer_id' => ['nullable', 'exists:customers,id', 'required_if:test_target_type,customer'],
        ]);

        $type = $request->input('test_target_type');
        $targetToken = null;

        if ($type === 'token') {
            $targetToken = $request->input('test_device_token');
        } elseif ($type === 'customer') {
            $customer = Customer::findOrFail($request->input('test_customer_id'));
            $tokens = $customer->routeNotificationForFcm();
            if (empty($tokens)) {
                return redirect()->route('admin.mobile-apps.index', ['tab' => 'push'])
                    ->with('error', "Customer '{$customer->name}' does not have any registered devices.");
            }
            $targetToken = $tokens[0];
        }

        $result = $this->firebase->testConnection($targetToken);

        if ($result['success']) {
            $msg = $targetToken
                ? 'Test push notification sent successfully to the device!'
                : ($result['message'] ?? 'Firebase connection verified successfully.');

            return redirect()->route('admin.mobile-apps.index', ['tab' => 'push'])
                ->with('success', $msg);
        }

        $errMsg = $result['error'] ?? $result['message'] ?? 'Failed to send test push notification.';

        return redirect()->route('admin.mobile-apps.index', ['tab' => 'push'])
            ->with('error', 'Firebase Error: ' . $errMsg);
    }

    /**
     * Broadcast a push notification to all customers or a specific customer.
     */
    public function broadcastPush(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:120'],
            'body' => ['required', 'string', 'max:500'],
            'audience' => ['required', 'in:all_customers,specific_customer'],
            'customer_id' => ['nullable', 'exists:customers,id', 'required_if:audience,specific_customer'],
            'image_url' => ['nullable', 'url', 'max:500'],
        ]);

        if (! $this->firebase->isEnabled()) {
            return redirect()->route('admin.mobile-apps.index', ['tab' => 'push'])
                ->with('error', 'Firebase notifications are currently disabled or not configured with credentials.');
        }

        $title = $validated['title'];
        $body = $validated['body'];
        $imageUrl = $validated['image_url'] ?? null;
        $data = [
            'type' => 'broadcast',
            'click_action' => 'OPEN_ANNOUNCEMENT',
            'sent_at' => now()->toISOString(),
        ];

        if ($validated['audience'] === 'specific_customer') {
            $customer = Customer::findOrFail($validated['customer_id']);
            $tokens = $customer->routeNotificationForFcm();

            if (empty($tokens)) {
                return redirect()->route('admin.mobile-apps.index', ['tab' => 'push'])
                    ->with('error', "Customer '{$customer->name}' does not have any active registered devices.");
            }

            $result = $this->firebase->sendToTokens($tokens, $title, $body, $data, $imageUrl);

            return redirect()->route('admin.mobile-apps.index', ['tab' => 'push'])
                ->with('success', "Push notification sent to {$customer->name} ({$result['success_count']} device(s) delivered).");
        }

        // Broadcast to all registered devices
        $allTokens = DeviceToken::pluck('token')->filter()->all();

        if (empty($allTokens)) {
            return redirect()->route('admin.mobile-apps.index', ['tab' => 'push'])
                ->with('error', 'No registered mobile devices found in the system to broadcast to.');
        }

        $result = $this->firebase->sendToTokens($allTokens, $title, $body, $data, $imageUrl);

        return redirect()->route('admin.mobile-apps.index', ['tab' => 'push'])
            ->with('success', "Broadcast sent to {$result['total']} registered devices ({$result['success_count']} delivered, {$result['failure_count']} failed).");
    }
}