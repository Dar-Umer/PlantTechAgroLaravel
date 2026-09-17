<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Indexes matching the landing page / feed query patterns
     * (filter by published/active flag, then sort).
     *
     * @var array<string, array<int, string>>
     */
    private array $indexes = [
        'services' => ['is_active', 'sort_order'],
        'partners' => ['is_active', 'sort_order'],
        'gallery_images' => ['is_active', 'sort_order'],
        'impact_stats' => ['is_active', 'sort_order'],
        'testimonials' => ['is_active', 'sort_order'],
        'lead_form_fields' => ['is_active', 'sort_order'],
        'posts' => ['is_published', 'published_at'],
        'projects' => ['is_published', 'is_featured', 'completed_at'],
        'home_sections' => ['section_key'],
    ];

    public function up(): void
    {
        foreach ($this->indexes as $table => $columns) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            Schema::table($table, function (Blueprint $blueprint) use ($columns) {
                $blueprint->index($columns);
            });
        }
    }

    public function down(): void
    {
        foreach ($this->indexes as $table => $columns) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            Schema::table($table, function (Blueprint $blueprint) use ($columns) {
                $blueprint->dropIndex($columns);
            });
        }
    }
};
