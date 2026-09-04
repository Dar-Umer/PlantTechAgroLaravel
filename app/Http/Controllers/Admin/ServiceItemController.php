<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\ServiceItem;
use Illuminate\Http\Request;

class ServiceItemController extends Controller
{
    public function index(Service $service)
    {
        $items = $service->items()->orderBy('sort_order')->get();

        return view('admin.services.items.index', compact('service', 'items'));
    }

    public function create(Service $service)
    {
        return view('admin.services.items.create', compact('service'));
    }

    public function store(Request $request, Service $service)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'max:2048'],
            'sort_order' => ['nullable', 'integer'],
        ]);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('service-items', 'public');
        }

        $service->items()->create($data);

        return redirect()->route('admin.services.items.index', $service)->with('success', 'Service item added.');
    }

    public function edit(ServiceItem $item)
    {
        $service = $item->service;

        return view('admin.services.items.edit', compact('service', 'item'));
    }

    public function update(Request $request, ServiceItem $item)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'max:2048'],
            'sort_order' => ['nullable', 'integer'],
        ]);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('service-items', 'public');
        } else {
            unset($data['image']);
        }

        $item->update($data);

        return redirect()->route('admin.services.items.index', $item->service_id)->with('success', 'Service item updated.');
    }

    public function destroy(ServiceItem $item)
    {
        $serviceId = $item->service_id;
        $item->delete();

        return redirect()->route('admin.services.items.index', $serviceId)->with('success', 'Service item deleted.');
    }
}
