@extends('layouts.app')

@php
    $firstBlockText = $content->blocks->first()
        ? \Illuminate\Support\Str::of($content->blocks->first()->body)->stripTags()->squish()->limit(160)->toString()
        : '';
@endphp

@section('title', "{$content->title} | Kockaklub")
@section('meta_description', $firstBlockText)

@section('content')
    <h1 class="text-2xl font-black text-gray-900 mb-6">{{ $content->title }}</h1>

    <div class="space-y-6">
        @foreach ($content->blocks as $block)
            <div class="prose prose-sm max-w-none text-gray-700">
                {!! $block->body !!}
            </div>
        @endforeach
    </div>
@endsection
