@extends('student.layout')
@section('title', $notice->title)
@section('content')
    <article><h1>{{ $notice->title }}</h1><p>{{ $notice->published_at?->format('Y-m-d') }}</p><div>{{ $notice->body }}</div></article>
@endsection