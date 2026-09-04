<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\LeadFormField;
use App\Models\Project;
use App\Models\Service;

class ProjectController extends Controller
{
    public function show(Project $project)
    {
        abort_unless($project->is_published, 404);

        $related = Project::published()
            ->whereKeyNot($project->id)
            ->orderByDesc('is_featured')
            ->orderByDesc('completed_at')
            ->take(3)
            ->get();

        return view('landing.project', [
            'project' => $project,
            'related' => $related,
            'services' => Service::active()->orderBy('sort_order')->get(),
            'leadFormFields' => LeadFormField::active()->get(),
        ]);
    }
}
