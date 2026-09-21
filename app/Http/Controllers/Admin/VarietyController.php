<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Variety;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class VarietyController extends Controller
{
    public function index(Request $request)
    {
        $query = Variety::query();

        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('category', 'like', "%{$search}%")
                  ->orWhere('season', 'like', "%{$search}%")
                  ->orWhere('taste', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category')) {
            $query->where('category', $request->get('category'));
        }

        $varieties = $query->orderBy('sort_order')->orderBy('name')->paginate(15)->withQueryString();

        $categories = Variety::query()
            ->whereNotNull('category')
            ->where('category', '!=', '')
            ->distinct()
            ->pluck('category');

        return view('admin.varieties.index', compact('varieties', 'categories'));
    }

    public function create()
    {
        return view('admin.varieties.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:100'],
            'season' => ['nullable', 'string', 'max:100'],
            'taste' => ['nullable', 'string', 'max:100'],
            'origin' => ['nullable', 'string', 'max:100'],
            'color' => ['nullable', 'string', 'max:100'],
            'storage_life' => ['nullable', 'string', 'max:100'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'short_description' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
            'is_featured' => ['boolean'],
            'is_active' => ['boolean'],
            'sort_order' => ['nullable', 'integer'],
        ]);

        if (isset($data['image']) && $data['image']) {
            $data['image'] = $request->file('image')->store('varieties', 'public');
        }

        $data['slug'] = Str::slug($data['name']);
        $data['is_active'] = $request->has('is_active');
        $data['is_featured'] = $request->has('is_featured');
        $data['sort_order'] = $data['sort_order'] ?? 0;

        Variety::create($data);

        return redirect()->route('admin.varieties.index')->with('success', 'Variety created successfully.');
    }

    public function edit(Variety $variety)
    {
        return view('admin.varieties.edit', compact('variety'));
    }

    public function update(Request $request, Variety $variety)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:100'],
            'season' => ['nullable', 'string', 'max:100'],
            'taste' => ['nullable', 'string', 'max:100'],
            'origin' => ['nullable', 'string', 'max:100'],
            'color' => ['nullable', 'string', 'max:100'],
            'storage_life' => ['nullable', 'string', 'max:100'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'short_description' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
            'is_featured' => ['boolean'],
            'is_active' => ['boolean'],
            'sort_order' => ['nullable', 'integer'],
        ]);

        if (isset($data['image']) && $data['image']) {
            $data['image'] = $request->file('image')->store('varieties', 'public');
        } else {
            unset($data['image']);
        }

        $data['slug'] = Str::slug($data['name']);
        $data['is_active'] = $request->has('is_active');
        $data['is_featured'] = $request->has('is_featured');
        $data['sort_order'] = $data['sort_order'] ?? 0;

        $variety->update($data);

        return redirect()->route('admin.varieties.index')->with('success', 'Variety updated successfully.');
    }

    public function toggleActive(Variety $variety)
    {
        $variety->is_active = ! $variety->is_active;
        $variety->save();

        $status = $variety->is_active ? 'activated' : 'deactivated';

        return back()->with('success', "Variety '{$variety->name}' {$status}.");
    }

    public function destroy(Variety $variety)
    {
        $variety->delete();

        return redirect()->route('admin.varieties.index')->with('success', 'Variety deleted successfully.');
    }
}