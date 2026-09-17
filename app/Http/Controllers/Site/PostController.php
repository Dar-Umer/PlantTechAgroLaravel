<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\LeadFormField;
use App\Models\Post;
use App\Models\Service;

class PostController extends Controller
{
    public function show(Post $post)
    {
        abort_unless($post->is_published, 404);

        $post->load(['category', 'author']);

        $related = Post::published()
            ->with('category')
            ->whereKeyNot($post->id)
            ->latest('published_at')
            ->take(3)
            ->get();

        return view('landing.post', [
            'post' => $post,
            'related' => $related,
            'services' => Service::active()->orderBy('sort_order')->get(),
            'leadFormFields' => LeadFormField::active()->get(),
        ]);
    }
}
