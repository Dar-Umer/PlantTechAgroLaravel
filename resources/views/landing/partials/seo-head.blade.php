@php
    $siteName = trim((string) (config('seo.og_site_name') ?: config('shop.site_name', 'Plant Tech Agro')));
    $metaTitle = trim((string) (config('seo.meta_title') ?: config('shop.site_name', 'Plant Tech Agro')));
    $metaDescription = trim((string) (config('seo.meta_description') ?: (config('shop.seo_meta_description') ?: config('shop.footer_tagline', ''))));
    $metaKeywords = trim((string) (config('seo.meta_keywords') ?: config('shop.seo_meta_keywords', '')));
    $metaAuthor = trim((string) config('seo.author', ''));
    $canonicalUrl = trim((string) config('seo.canonical_url', ''));
    $currentUrl = url()->current();
    $ogType = config('seo.og_type') ?: 'website';
    $ogTitle = trim((string) config('seo.og_title', ''));
    $ogDescription = trim((string) config('seo.og_description', ''));
    $ogImage = trim((string) (config('seo.og_image') ?: config('shop.seo_og_image', ''))) ?: \App\Support\Media::url(config('shop.logo_url'));
    $twitterCard = config('seo.twitter_card') ?: 'summary_large_image';
    $twitterSite = trim((string) config('seo.twitter_site', ''));
    $twitterTitle = trim((string) config('seo.twitter_title', ''));
    $twitterDescription = trim((string) config('seo.twitter_description', ''));
    $twitterImage = trim((string) config('seo.twitter_image', ''));
    $imageUrl = fn (?string $path = null) => $path ? \App\Support\Media::url($path) : null;
    $robotsRules = [];
    if (! (bool) config('seo.robots_index', true)) {
        $robotsRules[] = 'noindex';
    }
    if (! (bool) config('seo.robots_follow', true)) {
        $robotsRules[] = 'nofollow';
    }
    $schema = [
        '@context' => 'https://schema.org',
        '@type' => 'Organization',
        'name' => $siteName,
        'url' => url('/'),
        'description' => $metaDescription,
    ];
    $organizationLogo = $imageUrl(config('shop.logo_url'));
    if ($organizationLogo) {
        $schema['logo'] = $organizationLogo;
    }
    $phone = trim((string) config('shop.site_phone', ''));
    if ($phone) {
        $schema['telephone'] = $phone;
    }
    $address = trim((string) config('shop.site_address', ''));
    if ($address) {
        $schema['address'] = ['@type' => 'PostalAddress', 'streetAddress' => $address, 'addressCountry' => 'IN'];
    }
    $sameAs = array_values(array_filter([
        config('shop.social_facebook'),
        config('shop.social_instagram'),
        config('shop.social_youtube'),
        config('shop.social_x'),
        str_contains((string) config('shop.social_whatsapp'), 'http') ? config('shop.social_whatsapp') : null,
    ]));
    if ($sameAs) {
        $schema['sameAs'] = $sameAs;
    }
@endphp

@if($metaDescription)
<meta name="description" content="{{ $metaDescription }}">
@endif
@if($metaKeywords)
<meta name="keywords" content="{{ $metaKeywords }}">
@endif
@if($metaAuthor)
<meta name="author" content="{{ $metaAuthor }}">
@endif
@if($robotsRules)
<meta name="robots" content="{{ implode(',', $robotsRules) }}">
@endif
<link rel="canonical" href="{{ $canonicalUrl ?: $currentUrl }}">

@if((bool) config('seo.og_enabled', true))
<meta property="og:locale" content="{{ str_replace('-', '_', app()->getLocale()) }}">
<meta property="og:type" content="{{ $ogType }}">
<meta property="og:site_name" content="{{ $siteName }}">
<meta property="og:title" content="{{ $ogTitle ?: $metaTitle }}">
@if($ogDescription ?: $metaDescription)
<meta property="og:description" content="{{ $ogDescription ?: $metaDescription }}">
@endif
<meta property="og:url" content="{{ $currentUrl }}">
@if($ogImage)
<meta property="og:image" content="{{ $imageUrl($ogImage) }}">
@endif
@endif

@if((bool) config('seo.twitter_enabled', true))
<meta name="twitter:card" content="{{ $twitterCard }}">
@if($twitterSite)
<meta name="twitter:site" content="{{ $twitterSite }}">
@endif
<meta name="twitter:title" content="{{ $twitterTitle ?: ($ogTitle ?: $metaTitle) }}">
@if($twitterDescription ?: ($ogDescription ?: $metaDescription))
<meta name="twitter:description" content="{{ $twitterDescription ?: ($ogDescription ?: $metaDescription) }}">
@endif
@if($twitterImage ?: $ogImage)
<meta name="twitter:image" content="{{ $imageUrl($twitterImage ?: $ogImage) }}">
@endif
@endif

@if(trim((string) config('seo.google_site_verification', '')))
<meta name="google-site-verification" content="{{ config('seo.google_site_verification') }}">
@endif
@if(trim((string) config('seo.bing_site_verification', '')))
<meta name="msvalidate.01" content="{{ config('seo.bing_site_verification') }}">
@endif
@if(trim((string) config('seo.yandex_verification', '')))
<meta name="yandex-verification" content="{{ config('seo.yandex_verification') }}">
@endif

@if((bool) config('seo.schema_enabled', true))
<script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}</script>
@endif