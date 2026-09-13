@extends('public.layout')
@section('title', 'Notices')
@section('content')<section class="section"><div class="shell"><h1>Notices</h1><div class="grid">@forelse($notices as $notice)<article class="card"><p class="meta">{{ $notice->published_at?->format('M j, Y') }}</p><h2>{{ $notice->title }}</h2><p>{{ $notice->body }}</p></article>@empty<p>No notices published yet.</p>@endforelse</div>{{ $notices->links() }}</div></section>@endsection
