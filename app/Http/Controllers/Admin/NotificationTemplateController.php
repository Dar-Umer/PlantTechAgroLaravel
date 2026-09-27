<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\ServiceStage;
use App\Models\Setting;
use App\Services\NotificationTemplateService;
use Illuminate\Http\Request;

class NotificationTemplateController extends Controller
{
    public function index(Request $request)
    {
        $systemTemplates = NotificationTemplateService::all();
        $services = Service::with(['stages' => fn ($q) => $q->orderBy('sort_order')])->orderBy('name')->get();
        $activeTab = $request->query('tab', 'stages');

        return view('admin.notifications.templates', compact('systemTemplates', 'services', 'activeTab'));
    }

    public function updateSystem(Request $request)
    {
        $data = $request->validate([
            'templates' => ['required', 'array'],
            'templates.*.title' => ['required', 'string', 'max:150'],
            'templates.*.body' => ['required', 'string', 'max:1000'],
            'templates.*.fcm_enabled' => ['nullable', 'boolean'],
        ]);

        $saved = Setting::get('notification_templates', []);

        foreach ($data['templates'] as $key => $values) {
            $saved[$key] = [
                'title' => trim($values['title']),
                'body' => trim($values['body']),
                'fcm_enabled' => ! empty($values['fcm_enabled']),
            ];
        }

        Setting::updateOrCreate(
            ['key' => 'notification_templates'],
            ['value' => $saved]
        );

        return redirect()->route('admin.notification-templates.index', ['tab' => 'system'])
            ->with('success', 'System notification templates updated successfully.');
    }

    public function updateStage(Request $request, ServiceStage $stage)
    {
        $data = $request->validate([
            'notify_customer' => ['nullable', 'boolean'],
            'notification_title' => ['nullable', 'string', 'max:150'],
            'notification_body' => ['nullable', 'string', 'max:500'],
        ]);

        $stage->update([
            'notify_customer' => (bool) $request->boolean('notify_customer'),
            'notification_title' => $data['notification_title'] ? trim($data['notification_title']) : null,
            'notification_body' => $data['notification_body'] ? trim($data['notification_body']) : null,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Stage '{$stage->name}' notification template updated.",
            ]);
        }

        return back()->with('success', "Stage '{$stage->name}' notification template updated.");
    }
}
