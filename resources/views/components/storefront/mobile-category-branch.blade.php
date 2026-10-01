@props(['category', 'level' => 0])

<div x-data="{ open: false }">
    <div class="flex items-center justify-between">
        <a href="{{ route('catalog.index', ['category' => $category->slug]) }}"
           class="flex-1 py-2.5 {{ $level === 0 ? 'text-base font-bold text-gray-900' : 'text-sm font-medium text-gray-600' }} transition hover:text-accent-600 active:text-accent-600">
            {{ $category->name }}
        </a>
        @if ($category->children->isNotEmpty())
            <button type="button" @click="open = !open" :aria-expanded="open.toString()" aria-label="Alkategóriák"
                    class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full text-gray-400 transition hover:bg-accent-50 hover:text-accent-600">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transition-transform duration-300" :class="open ? '-rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.25">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                </svg>
            </button>
        @endif
    </div>

    @if ($category->children->isNotEmpty())
        <div x-show="open" x-cloak x-transition.duration.200ms class="ml-3 space-y-0.5 border-l-2 border-accent-100 pl-4">
            @foreach ($category->children as $child)
                <x-storefront.mobile-category-branch :category="$child" :level="$level + 1" />
            @endforeach
        </div>
    @endif
</div>
