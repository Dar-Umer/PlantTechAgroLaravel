<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="robots" content="noindex, nofollow">
<title>{{ $title ?? 'Sign in' }} - {{ config('shop.site_name', config('app.name', 'PTA Admin')) }}</title>
@if(config('shop.favicon_url'))
<link rel="icon" href="{{ \App\Support\Media::url(config('shop.favicon_url')) }}">
@endif
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family={{ $theme['fontGoogle'] }}&display=swap" rel="stylesheet">
<script src="https://cdn.tailwindcss.com"></script>
<script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
<style>
    [x-cloak] { display: none !important; }
</style>
<script>
    tailwind.config = {
        theme: {
            extend: {
                fontFamily: { sans: ['{{ $theme['font'] }}', 'system-ui', 'sans-serif'] },
                colors: {
                    brand: {
                        50:  '{{ $theme['palette'][50] }}',
                        100: '{{ $theme['palette'][100] }}',
                        200: '{{ $theme['palette'][200] }}',
                        300: '{{ $theme['palette'][300] }}',
                        400: '{{ $theme['palette'][400] }}',
                        500: '{{ $theme['palette'][500] }}',
                        600: '{{ $theme['palette'][600] }}',
                        700: '{{ $theme['palette'][700] }}',
                        800: '{{ $theme['palette'][800] }}',
                        900: '{{ $theme['palette'][900] }}',
                    }
                }
            }
        }
    }
</script>