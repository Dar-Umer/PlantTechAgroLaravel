<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Support\HtmlSanitizer;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ServiceController extends Controller
{
    public function index()
    {
        $services = Service::orderBy('sort_order')->paginate(15);

        return view('admin.services.index', compact('services'));
    }

    public function create()
    {
        return view('admin.services.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'content' => ['nullable', 'string'],
            'icon' => ['nullable', 'string', 'max:255'],
            'book_url' => ['nullable', 'string', 'max:255'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,gif', 'max:2048'],
            'sort_order' => ['nullable', 'integer'],
            'is_active' => ['boolean'],
            'creates_orchard_on_completion' => ['boolean'],
        ]);

        $data['slug'] = Str::slug($data['name']);
        $data['content'] = HtmlSanitizer::clean($data['content'] ?? null);
        $data['description'] = strip_tags((string) ($data['description'] ?? ''));
        $data['creates_orchard_on_completion'] = (bool) $request->boolean('creates_orchard_on_completion');

        if (isset($data['image']) && $data['image']) {
            $data['image'] = $request->file('image')->store('services', 'public');
        }

        Service::create($data);

        return redirect()->route('admin.services.index')->with('success', 'Service created.');
    }

    public function edit(Service $service)
    {
        return view('admin.services.edit', compact('service'));
    }

    public function update(Request $request, Service $service)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'content' => ['nullable', 'string'],
            'icon' => ['nullable', 'string', 'max:255'],
            'book_url' => ['nullable', 'string', 'max:255'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,gif', 'max:2048'],
            'sort_order' => ['nullable', 'integer'],
            'is_active' => ['boolean'],
            'creates_orchard_on_completion' => ['boolean'],
        ]);

        $data['creates_orchard_on_completion'] = (bool) $request->boolean('creates_orchard_on_completion');

        if (isset($data['image']) && $data['image']) {
            $data['image'] = $request->file('image')->store('services', 'public');
        } else {
            unset($data['image']);
        }

        $data['content'] = HtmlSanitizer::clean($data['content'] ?? $service->content);
        if (array_key_exists('description', $data)) {
            $data['description'] = strip_tags((string) $data['description']);
        }

        $service->update($data);

        return redirect()->route('admin.services.index')->with('success', 'Service updated.');
    }

    public function destroy(Service $service)
    {
        $service->delete();

        return redirect()->route('admin.services.index')->with('success', 'Service deleted.');
    }
}
