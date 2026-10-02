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
use App\Support\ContentCache;

class LandingController extends Controller
{
    public function index()
    {
        // If request reached here from a POS subdomain (e.g. pos.planttechagro.com), redirect to POS terminal
        $host = request()->getHost();
        $posHost = parse_url(config('pos.subdomain_url', 'https://pos.planttechagro.com'), PHP_URL_HOST);
        $posPort = (int) env('POS_LOCAL_PORT', 8001);

        if (str_starts_with($host, 'pos.') || ($posHost && $host === $posHost) || (int) request()->getPort() === $posPort) {
            if (\Illuminate\Support\Facades\Auth::guard('admin')->check()) {
                return redirect()->route('admin.pos.terminal');
            }

            return redirect('/login');
        }

        $data = ContentCache::remember('landing', function () {
            return [
                'sections' => HomeSection::query()->get()->keyBy('section_key'),
                'services' => Service::active()->orderBy('sort_order')->get(),
                'partners' => Partner::active()->orderBy('sort_order')->get(),
                'gallery' => GalleryImage::active()->orderBy('sort_order')->take(6)->get(),
                'stats' => ImpactStat::active()->orderBy('sort_order')->get(),
                'projects' => Project::published()
                    ->orderByDesc('is_featured')
                    ->orderByDesc('completed_at')
                    ->take(6)
                    ->get(),
                'posts' => Post::published()->with('category')->latest('published_at')->take(3)->get(),
                'testimonials' => Testimonial::active()->orderBy('sort_order')->get(),
                'leadFormFields' => LeadFormField::active()->get(),
                'marqueeTags' => [
                    'Apple Orchards', 'Drip Irrigation', 'Soil Testing', 'Orchard Booking',
                    'Ground Water Detection', 'Sustainable Farming', 'Agri Tech Solutions',
                    'Precision Farming', 'Premium Plants', 'Trellis Systems',
                ],
            ];
        });

        return view('landing.index', $data);
    }
}
