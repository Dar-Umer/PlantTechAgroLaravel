<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\HomeSection;
use App\Models\Project;
use App\Models\Service;
use App\Models\Variety;
use App\Support\ContentCache;

class VarietyController extends Controller
{
    /**
     * Show the public varieties catalogue page.
     */
    public function index()
    {
        $data = ContentCache::remember('varieties', function () {
            $varieties = Variety::active()
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get();

            return [
                'sections' => HomeSection::query()->get()->keyBy('section_key'),
                'varieties' => $varieties,
                'featured' => $varieties->firstWhere('is_featured', true) ?? $varieties->first(),
                'services' => Service::active()->orderBy('sort_order')->get(),
                'relatedProjects' => Project::published()
                    ->orderByDesc('is_featured')
                    ->orderByDesc('completed_at')
                    ->take(3)
                    ->get(),
            ];
        });

        return view('landing.varieties', $data);
    }
}