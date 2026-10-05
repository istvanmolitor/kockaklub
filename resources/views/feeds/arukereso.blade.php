<?xml version="1.0" encoding="UTF-8"?>
<SHOP>
    @foreach ($products as $product)
        @php
            $categoryText = $product->category->ancestors()
                ->push($product->category)
                ->map(fn ($category) => $category->name)
                ->implode('>');

            $description = \Illuminate\Support\Str::of((string) $product->description)->stripTags()->squish()->limit(2000)->toString();

            $images = $product->images->isNotEmpty()
                ? $product->images->map(fn ($image) => $image->url())->values()
                : collect([$product->default_image_url]);

            $brand = $brandsByProductId->get($product->id);
            $ean = $product->barcodes->first()?->barcode;
            $deliveryDate = $product->public_stock > 0 ? 0 : 3;
        @endphp
        <SHOPITEM>
            <ITEM_ID>{{ $product->id }}</ITEM_ID>
            <PRODUCTNAME>{{ $product->name }}</PRODUCTNAME>
            @if ($description !== '')
                <DESCRIPTION>{{ $description }}</DESCRIPTION>
            @endif
            <URL>{{ route('catalog.show', $product) }}</URL>
            <IMGURL>{{ $images->first() }}</IMGURL>
            @foreach ($images->slice(1) as $alternativeImage)
                <IMGURL_ALTERNATIVE>{{ $alternativeImage }}</IMGURL_ALTERNATIVE>
            @endforeach
            <PRICE_VAT>{{ $product->price }}</PRICE_VAT>
            <VAT>{{ $product->vat_rate }}</VAT>
            <CURRENCY>HUF</CURRENCY>
            <CATEGORYTEXT>{{ $categoryText }}</CATEGORYTEXT>
            @if ($brand)
                <MANUFACTURER>{{ $brand }}</MANUFACTURER>
            @endif
            @if ($ean)
                <EAN>{{ $ean }}</EAN>
            @endif
            <DELIVERY_DATE>{{ $deliveryDate }}</DELIVERY_DATE>
            <DELIVERY_COST>{{ $deliveryCost }}</DELIVERY_COST>
        </SHOPITEM>
    @endforeach
</SHOP>
