<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Variety;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class VarietyController extends Controller
{
    public function index()
    {
        $varieties = Variety::orderBy('sort_order')->paginate(15);

        return view('admin.varieties.index', compact('varieties'));
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
            'image' => ['nullable', 'image', 'max:2048'],
            'short_description' => ['nullable', 'string'],
            'is_active' => ['boolean'],
            'sort_order' => ['nullable', 'integer'],
        ]);

        if (isset($data['image']) && $data['image']) {
            $data['image'] = $request->file('image')->store('varieties', 'public');
        }

        $data['slug'] = Str::slug($data['name']);

        Variety::create($data);

        return redirect()->route('admin.varieties.index')->with('success', 'Variety created.');
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
            'image' => ['nullable', 'image', 'max:2048'],
            'short_description' => ['nullable', 'string'],
            'is_active' => ['boolean'],
            'sort_order' => ['nullable', 'integer'],
        ]);

        if (isset($data['image']) && $data['image']) {
            $data['image'] = $request->file('image')->store('varieties', 'public');
        } else {
            unset($data['image']);
        }

        $data['slug'] = Str::slug($data['name']);

        $variety->update($data);

        return redirect()->route('admin.varieties.index')->with('success', 'Variety updated.');
    }

    public function destroy(Variety $variety)
    {
        $variety->delete();

        return redirect()->route('admin.varieties.index')->with('success', 'Variety deleted.');
    }
}