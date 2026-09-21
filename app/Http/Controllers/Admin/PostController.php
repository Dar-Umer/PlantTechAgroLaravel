<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\PostCategory;
use App\Support\HtmlSanitizer;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PostController extends Controller
{
    public function index(Request $request)
    {
        $query = Post::with(['category', 'author'])->latest();

        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('excerpt', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->get('category_id'));
        }

        if ($request->filled('status')) {
            $status = $request->get('status');
            if ($status === 'published') {
                $query->where('is_published', true);
            } elseif ($status === 'draft') {
                $query->where('is_published', false);
            }
        }

        $posts = $query->paginate(15)->withQueryString();

        $stats = [
            'total' => Post::count(),
            'published' => Post::where('is_published', true)->count(),
            'drafts' => Post::where('is_published', false)->count(),
            'categories' => PostCategory::count(),
        ];

        $categories = PostCategory::orderBy('name')->get();

        return view('admin.posts.index', compact('posts', 'stats', 'categories'));
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
            'featured_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,gif', 'max:2048'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string'],
            'is_published' => ['boolean'],
            'published_at' => ['nullable', 'date'],
        ]);

        $data['slug'] = Str::slug($data['title']);
        $data['author_id'] = auth()->guard('admin')->id();
        $data['content'] = HtmlSanitizer::clean($data['content'] ?? null);
        $data['excerpt'] = strip_tags((string) ($data['excerpt'] ?? ''));

        if (isset($data['featured_image']) && $data['featured_image']) {
            $data['featured_image'] = $request->file('featured_image')->store('posts', 'public');
        }

        if (! empty($data['is_published']) && empty($data['published_at'])) {
            $data['published_at'] = now();
        }

        Post::create($data);

        return redirect()->route('admin.posts.index')->with('success', 'Post created successfully.');
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
            'featured_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,gif', 'max:2048'],
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

        $data['content'] = HtmlSanitizer::clean($data['content'] ?? $post->content);
        if (array_key_exists('excerpt', $data)) {
            $data['excerpt'] = strip_tags((string) $data['excerpt']);
        }

        if (! empty($data['is_published']) && empty($post->published_at) && empty($data['published_at'])) {
            $data['published_at'] = now();
        }

        $post->update($data);

        return redirect()->route('admin.posts.index')->with('success', 'Post updated successfully.');
    }

    public function togglePublish(Post $post)
    {
        $post->is_published = ! $post->is_published;
        if ($post->is_published && empty($post->published_at)) {
            $post->published_at = now();
        }
        $post->save();

        $statusLabel = $post->is_published ? 'published' : 'moved to drafts';

        return back()->with('success', "Post '{$post->title}' {$statusLabel}.");
    }

    public function storeCategory(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:post_categories,name'],
            'description' => ['nullable', 'string'],
        ]);

        $data['slug'] = Str::slug($data['name']);

        PostCategory::create($data);

        return back()->with('success', "Category '{$data['name']}' created successfully.");
    }

    public function destroy(Post $post)
    {
        $post->delete();

        return redirect()->route('admin.posts.index')->with('success', 'Post deleted successfully.');
    }
}
