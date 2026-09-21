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

        $previous = Post::published()
            ->where('published_at', '<', $post->published_at ?? $post->created_at)
            ->whereKeyNot($post->id)
            ->latest('published_at')
            ->first();

        $next = Post::published()
            ->where('published_at', '>', $post->published_at ?? $post->created_at)
            ->whereKeyNot($post->id)
            ->oldest('published_at')
            ->first();

        $related = Post::published()
            ->with('category')
            ->whereKeyNot($post->id)
            ->when($post->category_id, function ($q) use ($post) {
                $q->where('category_id', $post->category_id);
            })
            ->latest('published_at')
            ->take(3)
            ->get();

        if ($related->count() < 3) {
            $extra = Post::published()
                ->with('category')
                ->whereKeyNot($post->id)
                ->whereNotIn('id', $related->pluck('id'))
                ->latest('published_at')
                ->take(3 - $related->count())
                ->get();
            $related = $related->concat($extra);
        }

        // Calculate reading time (avg 200 wpm)
        $wordCount = str_word_count(strip_tags((string) $post->content));
        $readingTime = max(1, (int) ceil($wordCount / 200));

        return view('landing.post', [
            'post' => $post,
            'related' => $related,
            'previous' => $previous,
            'next' => $next,
            'readingTime' => $readingTime,
            'services' => Service::active()->orderBy('sort_order')->get(),
            'leadFormFields' => LeadFormField::active()->get(),
        ]);
    }
}
