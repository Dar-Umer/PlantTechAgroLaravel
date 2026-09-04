<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class PostCategory extends Model
{
    protected $fillable = ['name', 'slug', 'description', 'sort_order'];

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    protected static function booted(): void
    {
        static::creating(function (PostCategory $category) {
            if (empty($category->slug)) {
                $category->slug = Str::slug($category->name);
            }
        });
    }
}
