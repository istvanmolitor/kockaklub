@php
    $metaDescription = trim($__env->yieldContent('meta_description', setting('seo_home_description', '')));
    $canonicalUrl = trim($__env->yieldContent('canonical', url()->current()));
    $robots = trim($__env->yieldContent('robots', 'index,follow'));
    $ogType = trim($__env->yieldContent('og_type', 'website'));
    $ogImage = trim($__env->yieldContent('og_image', asset('images/logo.png')));
    $ogTitle = trim($__env->yieldContent('title', 'Kockaklub'));
@endphp

@if ($metaDescription !== '')
    <meta name="description" content="{{ $metaDescription }}">
@endif
<meta name="robots" content="{{ $robots }}">
<link rel="canonical" href="{{ $canonicalUrl }}">

<meta property="og:site_name" content="{{ setting('company_name', 'Kockaklub') }}">
<meta property="og:locale" content="hu_HU">
<meta property="og:type" content="{{ $ogType }}">
<meta property="og:title" content="{{ $ogTitle }}">
@if ($metaDescription !== '')
    <meta property="og:description" content="{{ $metaDescription }}">
@endif
<meta property="og:url" content="{{ $canonicalUrl }}">
<meta property="og:image" content="{{ $ogImage }}">

<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $ogTitle }}">
@if ($metaDescription !== '')
    <meta name="twitter:description" content="{{ $metaDescription }}">
@endif
<meta name="twitter:image" content="{{ $ogImage }}">

<script type="application/ld+json">
{!! json_encode([
    '@@context' => 'https://schema.org',
    '@graph' => [
        [
            '@type' => 'Organization',
            '@id' => url('/#organization'),
            'name' => setting('company_name', 'Kockaklub'),
            'url' => url('/'),
            'logo' => asset('images/logo.png'),
            'contactPoint' => array_filter([
                '@type' => 'ContactPoint',
                'telephone' => setting('contact_phone'),
                'email' => setting('contact_email'),
                'contactType' => 'customer service',
            ]),
            'sameAs' => array_values(array_filter([
                setting('facebook_url'),
                setting('instagram_url'),
                setting('youtube_url'),
            ])),
        ],
        [
            '@type' => 'WebSite',
            '@id' => url('/#website'),
            'name' => setting('company_name', 'Kockaklub'),
            'url' => url('/'),
            'inLanguage' => 'hu-HU',
            'publisher' => ['@id' => url('/#organization')],
            'potentialAction' => [
                '@type' => 'SearchAction',
                'target' => [
                    '@type' => 'EntryPoint',
                    'urlTemplate' => route('search.index').'?q={search_term_string}',
                ],
                'query-input' => 'required name=search_term_string',
            ],
        ],
    ],
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
</script>

@stack('structured_data')
