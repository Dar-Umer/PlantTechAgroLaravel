<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\GalleryImage;
use App\Models\HomeSection;
use App\Models\ImpactStat;
use App\Models\LeadFormField;
use App\Models\Partner;
use App\Models\Post;
use App\Models\Project;
use App\Models\Service;
use App\Models\Testimonial;

class LandingController extends Controller
{
    public function index()
    {
        $homeSections = HomeSection::query()->get()->keyBy('section_key');

        $data = [
            'sections' => $homeSections,
            'services' => Service::active()->orderBy('sort_order')->get(),
            'partners' => Partner::active()->orderBy('sort_order')->get(),
            'gallery' => GalleryImage::active()->orderBy('sort_order')->take(6)->get(),
            'stats' => ImpactStat::active()->orderBy('sort_order')->get(),
            'projects' => Project::published()->featured()->orderBy('completed_at', 'desc')->take(6)->get(),
            'posts' => Post::published()->latest('published_at')->take(3)->get(),
            'testimonials' => Testimonial::active()->orderBy('sort_order')->get(),
            'leadFormFields' => LeadFormField::active()->get(),
            'marqueeTags' => [
                'Apple Orchards', 'Drip Irrigation', 'Soil Testing', 'Orchard Booking',
                'Ground Water Detection', 'Sustainable Farming', 'Agri Tech Solutions',
                'Precision Farming', 'Premium Plants', 'Trellis Systems',
            ],
        ];

        return view('landing.index', $data);
    }
}
