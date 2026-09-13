@extends('public.layout')
@section('title', $article->title)
@section('content')<section class="section"><div class="shell prose"><p class="meta">{{ $article->published_at?->format('M j, Y') }}</p><h1>{{ $article->title }}</h1><div>{{ $article->body }}</div><p><a href="{{ route('public.news') }}">Back to news</a></p></div></section>@endsection
