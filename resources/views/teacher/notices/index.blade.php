@extends('teacher.layout')
@section('title', 'Notices')
@section('content')<h1>Notices</h1>@forelse($notices as $notice)<article class="panel"><h2>{{ $notice->title }}</h2><p>{{ $notice->body }}</p></article>@empty<p>No notices.</p>@endforelse{{ $notices->links() }}@endsection