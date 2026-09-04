<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\ServiceStage;
use Illuminate\Http\Request;

class ServiceStageController extends Controller
{
    public function index(Service $service)
    {
        $stages = $service->stages()->orderBy('sort_order')->get();

        return view('admin.services.stages.index', compact('service', 'stages'));
    }

    public function create(Service $service)
    {
        return view('admin.services.stages.create', compact('service'));
    }

    public function store(Request $request, Service $service)
    {
        $data = $this->validated($request);

        $service->stages()->create($data);

        return redirect()->route('admin.services.stages.index', $service)->with('success', 'Stage added.');
    }

    public function edit(ServiceStage $stage)
    {
        $service = $stage->service;

        return view('admin.services.stages.edit', compact('service', 'stage'));
    }

    public function update(Request $request, ServiceStage $stage)
    {
        $stage->update($this->validated($request));

        return redirect()->route('admin.services.stages.index', $stage->service_id)->with('success', 'Stage updated.');
    }

    public function destroy(ServiceStage $stage)
    {
        $serviceId = $stage->service_id;
        $stage->delete();

        return redirect()->route('admin.services.stages.index', $serviceId)->with('success', 'Stage deleted.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'sort_order' => ['nullable', 'integer'],
            'requires_photo' => ['nullable', 'boolean'],
            'min_photos' => ['nullable', 'integer', 'min:1', 'max:20'],
            'requires_pdf' => ['nullable', 'boolean'],
        ]);
    }
}
