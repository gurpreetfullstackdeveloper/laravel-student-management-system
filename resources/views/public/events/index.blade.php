@extends('public.layout')
@section('title', 'Events')
@section('content')<section class="section"><div class="shell"><h1>Events</h1><div class="grid">@forelse($events as $event)<article class="card"><p class="meta">{{ $event->published_at?->format('M j, Y') }}</p><h2><a href="{{ route('public.events.show', $event->slug) }}">{{ $event->title }}</a></h2><p>{{ \Illuminate\Support\Str::limit($event->body, 150) }}</p></article>@empty<p>No events published yet.</p>@endforelse</div>{{ $events->links() }}</div></section>@endsection
