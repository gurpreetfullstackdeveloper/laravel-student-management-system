@extends('student.layout')
@section('title', 'Notices')
@section('content')
    <h1>Notices</h1>
    @forelse ($notices as $notice)
        <article class="panel"><h2>{{ $notice->title }}</h2><p>{{ $notice->published_at?->format('Y-m-d') }}</p><a href="{{ route('student.notices.show', $notice) }}">Read notice</a></article>
    @empty <p>No notices available.</p> @endforelse
    {{ $notices->links() }}
@endsection