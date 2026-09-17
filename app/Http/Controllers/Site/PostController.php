<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Post;

class PostController extends Controller
{
    public function show(Post $post)
    {
        abort_unless($post->is_published, 404);

        $related = Post::published()
            ->whereKeyNot($post->id)
            ->latest('published_at')
            ->take(3)
            ->get();

        return view('landing.post', [
            'post' => $post,
            'related' => $related,
        ]);
    }
}