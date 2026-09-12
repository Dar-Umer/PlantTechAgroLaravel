<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Support\Media;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function index(Request $request)
    {
        $services = Service::active()->orderBy('sort_order')->get();

        return response()->json([
            'services' => $services->map(fn (Service $service) => [
                'id' => $service->id,
                'name' => $service->name,
                'slug' => $service->slug,
                'description' => $service->description,
                'category' => $service->category,
                'icon' => $service->icon,
                'image' => Media::url($service->image),
                'sort_order' => $service->sort_order,
            ])->values(),
        ]);
    }
}