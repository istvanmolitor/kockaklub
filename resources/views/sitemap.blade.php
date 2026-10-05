<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
        xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">
    <url>
        <loc>{{ route('home') }}</loc>
        <changefreq>daily</changefreq>
        <priority>1.0</priority>
    </url>
    <url>
        <loc>{{ route('catalog.index') }}</loc>
        <changefreq>daily</changefreq>
        <priority>0.9</priority>
    </url>
    <url>
        <loc>{{ route('contact.create') }}</loc>
        <changefreq>monthly</changefreq>
        <priority>0.3</priority>
    </url>

    @foreach ($categories as $category)
        <url>
            <loc>{{ route('catalog.index', ['category' => $category->slug]) }}</loc>
            <lastmod>{{ $category->updated_at->toAtomString() }}</lastmod>
            <changefreq>weekly</changefreq>
            <priority>0.7</priority>
        </url>
    @endforeach

    @foreach ($products as $product)
        <url>
            <loc>{{ route('catalog.show', $product) }}</loc>
            <lastmod>{{ $product->updated_at->toAtomString() }}</lastmod>
            <changefreq>weekly</changefreq>
            <priority>0.8</priority>
            <image:image>
                <image:loc>{{ $product->default_image_url }}</image:loc>
            </image:image>
        </url>
    @endforeach

    @foreach ($contents as $content)
        <url>
            <loc>{{ route('content.show', $content) }}</loc>
            <lastmod>{{ $content->updated_at->toAtomString() }}</lastmod>
            <changefreq>monthly</changefreq>
            <priority>0.4</priority>
        </url>
    @endforeach
</urlset>
