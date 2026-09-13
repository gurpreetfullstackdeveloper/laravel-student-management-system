@extends('public.layout')
@section('title', 'Faculty')
@section('content')<section class="section"><div class="shell"><p class="meta">People who teach here</p><h1>Faculty</h1><div class="grid">@forelse($teachers as $teacher)<article class="card"><h2>{{ $teacher->user->name }}</h2><p>{{ $teacher->qualification ?? 'Faculty member' }}</p><p class="meta">{{ $teacher->employee_code }}</p></article>@empty<p>Faculty profiles will be published soon.</p>@endforelse</div>{{ $teachers->links() }}</div></section>@endsection
