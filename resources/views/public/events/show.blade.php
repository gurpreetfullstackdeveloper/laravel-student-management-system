@extends('public.layout')
@section('title', $event->title)
@section('content')<section class="section"><div class="shell prose"><p class="meta">{{ $event->published_at?->format('M j, Y') }}</p><h1>{{ $event->title }}</h1><div>{{ $event->body }}</div><p><a href="{{ route('public.events') }}">Back to events</a></p></div></section>@endsection
