<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GalleryImage;
use Illuminate\Http\Request;

class GalleryController extends Controller
{
    public function index()
    {
        $images = GalleryImage::orderBy('sort_order')->paginate(24);

        return view('admin.gallery.index', compact('images'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'image' => ['required', 'image', 'max:4096'],
            'caption' => ['nullable', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:255'],
        ]);

        $path = $request->file('image')->store('gallery', 'public');

        GalleryImage::create([
            'image' => $path,
            'caption' => $request->input('caption'),
            'category' => $request->input('category'),
            'sort_order' => GalleryImage::max('sort_order') + 1,
        ]);

        return redirect()->route('admin.gallery.index')->with('success', 'Image uploaded.');
    }

    public function destroy(GalleryImage $image)
    {
        $image->delete();

        return redirect()->route('admin.gallery.index')->with('success', 'Image deleted.');
    }
}
