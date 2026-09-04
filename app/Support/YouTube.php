<?php

namespace App\Support;

class YouTube
{
    /**
     * Minimal player: no control bar (timeline), no fullscreen button,
     * no annotations, no related videos, minimal branding.
     */
    public const PLAYER_PARAMS = 'controls=0&rel=0&fs=0&iv_load_policy=3&disablekeys=1&modestbranding=1';

    public static function embedUrl(?string $url): ?string
    {
        if (! $url) {
            return null;
        }

        $url = trim($url);

        if (! preg_match('~(?:youtube\.com|youtu\.be)~i', $url)) {
            return null;
        }

        // https://www.youtube.com/watch?v=VIDEO_ID
        if (preg_match('~[?&]v=([A-Za-z0-9_-]{6,20})~', $url, $m)) {
            return 'https://www.youtube.com/embed/' . $m[1];
        }

        // https://youtu.be/ID, /embed/ID, /shorts/ID, /live/ID
        if (preg_match('~(?:youtu\.be|youtube\.com/(?:embed|shorts|live))/([A-Za-z0-9_-]{6,20})~i', $url, $m)) {
            return 'https://www.youtube.com/embed/' . $m[1];
        }

        return null;
    }

    public static function embedSrc(?string $url): ?string
    {
        $embed = self::embedUrl($url);

        return $embed ? $embed . '?' . self::PLAYER_PARAMS : null;
    }
}
