@extends('layouts.app')

@section('title', $content->title)

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
