@extends('public.layout')
@section('title', 'News')
@section('content')<section class="section"><div class="shell"><h1>News</h1><div class="grid">@forelse($news as $article)<article class="card"><p class="meta">{{ $article->published_at?->format('M j, Y') }}</p><h2><a href="{{ route('public.news.show', $article->slug) }}">{{ $article->title }}</a></h2><p>{{ \Illuminate\Support\Str::limit($article->body, 150) }}</p></article>@empty<p>No news published yet.</p>@endforelse</div>{{ $news->links() }}</div></section>@endsection
