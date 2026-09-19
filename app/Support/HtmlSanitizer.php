<?php

namespace App\Support;

class HtmlSanitizer
{
    /**
     * Strip dangerous HTML while keeping basic formatting for CMS content.
     * No external dependency: allowlist-based via strip_tags + attribute scrub.
     */
    public static function clean(?string $html): string
    {
        if ($html === null || $html === '') {
            return '';
        }

        // Remove script/style/iframe/object/embed + event handlers in one pass.
        $html = preg_replace('#<(script|style|iframe|object|embed|link|meta|base|form|input|button|textarea|select|option)[^>]*?>.*?</\\1\s*>#is', '', $html) ?? '';
        $html = preg_replace('#<(script|style|iframe|object|embed|link|meta|base)[^>]*/?>#i', '', $html) ?? '';

        // Remove event-handler attributes (onclick=, onerror=, ...) and javascript: URIs.
        $html = preg_replace('/\s+on\w+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $html) ?? '';
        $html = preg_replace('/(href|src|xlink:href)\s*=\s*([\'"]?)\s*javascript:[^>"\']*\\2/i', '$1="#"', $html) ?? '';

        $allowed = '<p><br><b><strong><i><em><u><ul><ol><li><h2><h3><h4><blockquote><a><img><table><thead><tbody><tr><th><td><pre><code><hr><span><div>';
        $html = strip_tags($html, $allowed);

        // Only allow http/https/mailto in links and http/https/data in images.
        $html = preg_replace_callback('/<(a|img)([^>]*)>/i', function ($m) {
            $tag = strtolower($m[1]);
            $attrs = $m[2];

            if ($tag === 'a') {
                preg_match_all('/(href|title|target)\s*=\s*("[^"]*"|\'[^\']*\')/i', $attrs, $matches, PREG_SET_ORDER);
                $out = '';
                foreach ($matches as $attr) {
                    $name = strtolower($attr[1]);
                    $val = trim($attr[2], "\"'");
                    if ($name === 'href' && ! preg_match('#^(https?://|mailto:|/#)i', $val)) {
                        continue;
                    }
                    if ($name === 'target' && ! in_array(strtolower($val), ['_blank', '_self'], true)) {
                        continue;
                    }
                    $out .= ' '.$name.'="'.e($val).'"';
                }
                // Force safe link behaviour.
                if (str_contains($out, 'target="_blank"') && ! str_contains($out, 'rel=')) {
                    $out .= ' rel="noopener noreferrer"';
                }

                return '<a'.$out.'>';
            }

            preg_match_all('/(src|alt|title|width|height|loading)\s*=\s*("[^"]*"|\'[^\']*\')/i', $attrs, $matches, PREG_SET_ORDER);
            $out = '';
            foreach ($matches as $attr) {
                $name = strtolower($attr[1]);
                $val = trim($attr[2], "\"'");
                if ($name === 'src' && ! preg_match('#^(https?://|/storage/|/images/)#i', $val)) {
                    continue;
                }
                $out .= ' '.$name.'="'.e($val).'"';
            }

            return '<img'.$out.'>';
        }, $html) ?? '';

        return $html;
    }
}
