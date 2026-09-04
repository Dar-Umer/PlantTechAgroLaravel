<?php

namespace Tests\Unit;

use App\Support\YouTube;
use PHPUnit\Framework\TestCase;

class YouTubeTest extends TestCase
{
    public function test_parses_standard_watch_urls(): void
    {
        $this->assertSame(
            'https://www.youtube.com/embed/dQw4w9WgXcQ',
            YouTube::embedUrl('https://www.youtube.com/watch?v=dQw4w9WgXcQ')
        );
        $this->assertSame(
            'https://www.youtube.com/embed/dQw4w9WgXcQ',
            YouTube::embedUrl('https://www.youtube.com/watch?app=desktop&v=dQw4w9WgXcQ&t=42s')
        );
    }

    public function test_parses_short_and_alternate_url_formats(): void
    {
        $this->assertSame(
            'https://www.youtube.com/embed/dQw4w9WgXcQ',
            YouTube::embedUrl('https://youtu.be/dQw4w9WgXcQ')
        );
        $this->assertSame(
            'https://www.youtube.com/embed/dQw4w9WgXcQ',
            YouTube::embedUrl('https://www.youtube.com/embed/dQw4w9WgXcQ')
        );
        $this->assertSame(
            'https://www.youtube.com/embed/dQw4w9WgXcQ',
            YouTube::embedUrl('https://www.youtube.com/shorts/dQw4w9WgXcQ')
        );
    }

    public function test_returns_null_for_invalid_input(): void
    {
        $this->assertNull(YouTube::embedUrl(null));
        $this->assertNull(YouTube::embedUrl(''));
        $this->assertNull(YouTube::embedUrl('https://vimeo.com/123456789'));
        $this->assertNull(YouTube::embedUrl('https://www.youtube.com/playlist?list=PL1234'));
    }
}
