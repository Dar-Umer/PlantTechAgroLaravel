<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProjectController extends Controller
{
    public function index()
    {
        $projects = Project::latest()->paginate(15);

        return view('admin.projects.index', compact('projects'));
    }

    public function create()
    {
        return view('admin.projects.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'content' => ['nullable', 'string'],
            'featured_image' => ['nullable', 'image', 'max:2048'],
            'gallery' => ['nullable', 'array'],
            'gallery.*' => ['image', 'max:2048'],
            'location' => ['nullable', 'string', 'max:255'],
            'completed_at' => ['nullable', 'date'],
            'is_featured' => ['boolean'],
            'is_published' => ['boolean'],
        ]);

        $data['slug'] = Str::slug($data['title']);

        if (isset($data['featured_image']) && $data['featured_image']) {
            $data['featured_image'] = $request->file('featured_image')->store('projects', 'public');
        }

        if (! empty($data['gallery'])) {
            $paths = [];
            foreach ($data['gallery'] as $file) {
                $paths[] = $file->store('projects/gallery', 'public');
            }
            $data['gallery'] = $paths;
        }

        Project::create($data);

        return redirect()->route('admin.projects.index')->with('success', 'Project created.');
    }

    public function edit(Project $project)
    {
        return view('admin.projects.edit', compact('project'));
    }

    public function update(Request $request, Project $project)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'content' => ['nullable', 'string'],
            'featured_image' => ['nullable', 'image', 'max:2048'],
            'location' => ['nullable', 'string', 'max:255'],
            'completed_at' => ['nullable', 'date'],
            'is_featured' => ['boolean'],
            'is_published' => ['boolean'],
        ]);

        if (isset($data['featured_image']) && $data['featured_image']) {
            $data['featured_image'] = $request->file('featured_image')->store('projects', 'public');
        } else {
            unset($data['featured_image']);
        }

        $project->update($data);

        return redirect()->route('admin.projects.index')->with('success', 'Project updated.');
    }

    public function destroy(Project $project)
    {
        $project->delete();

        return redirect()->route('admin.projects.index')->with('success', 'Project deleted.');
    }
}
