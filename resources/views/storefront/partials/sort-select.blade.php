@php
    $sort = request('sort', 'newest');
@endphp

<form method="GET" class="flex items-center gap-2 text-sm">
    @foreach (request()->except(['sort', 'page']) as $key => $value)
        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
    @endforeach

    <label for="sort" class="font-semibold text-gray-600 whitespace-nowrap">Rendezés:</label>
    <select id="sort" name="sort" onchange="this.form.submit()" class="select-field">
        @foreach (\App\Models\Product::SORT_OPTIONS as $value => $label)
            <option value="{{ $value }}" @selected($sort === $value)>{{ $label }}</option>
        @endforeach
    </select>
</form>
