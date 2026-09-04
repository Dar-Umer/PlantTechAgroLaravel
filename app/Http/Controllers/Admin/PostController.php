<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\PostCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PostController extends Controller
{
    public function index(Request $request)
    {
        $posts = Post::with('category')->latest()->paginate(15);

        return view('admin.posts.index', compact('posts'));
    }

    public function create()
    {
        $categories = PostCategory::orderBy('name')->get();

        return view('admin.posts.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'category_id' => ['nullable', 'exists:post_categories,id'],
            'excerpt' => ['nullable', 'string'],
            'content' => ['nullable', 'string'],
            'featured_image' => ['nullable', 'image', 'max:2048'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string'],
            'is_published' => ['boolean'],
            'published_at' => ['nullable', 'date'],
        ]);

        $data['slug'] = Str::slug($data['title']);
        $data['author_id'] = auth()->guard('admin')->id();

        if (isset($data['featured_image']) && $data['featured_image']) {
            $data['featured_image'] = $request->file('featured_image')->store('posts', 'public');
        }

        if (! empty($data['is_published']) && empty($data['published_at'])) {
            $data['published_at'] = now();
        }

        Post::create($data);

        return redirect()->route('admin.posts.index')->with('success', 'Post created.');
    }

    public function edit(Post $post)
    {
        $categories = PostCategory::orderBy('name')->get();

        return view('admin.posts.edit', compact('post', 'categories'));
    }

    public function update(Request $request, Post $post)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'category_id' => ['nullable', 'exists:post_categories,id'],
            'excerpt' => ['nullable', 'string'],
            'content' => ['nullable', 'string'],
            'featured_image' => ['nullable', 'image', 'max:2048'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string'],
            'is_published' => ['boolean'],
            'published_at' => ['nullable', 'date'],
        ]);

        if (isset($data['featured_image']) && $data['featured_image']) {
            $data['featured_image'] = $request->file('featured_image')->store('posts', 'public');
        } else {
            unset($data['featured_image']);
        }

        $post->update($data);

        return redirect()->route('admin.posts.index')->with('success', 'Post updated.');
    }

    public function destroy(Post $post)
    {
        $post->delete();

        return redirect()->route('admin.posts.index')->with('success', 'Post deleted.');
    }
}
