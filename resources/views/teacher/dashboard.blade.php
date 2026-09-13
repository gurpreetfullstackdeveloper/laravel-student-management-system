@extends('teacher.layout')
@section('title', 'Teacher Dashboard')
@section('content')
<h1>Teacher Dashboard</h1>
<p>Welcome, {{ $teacher->user->name }}.</p>
<div class="grid">
    <div class="panel"><strong>Assigned classes</strong><br>{{ $classIds->count() }}</div>
    <div class="panel"><strong>Teaching assignments</strong><br>{{ $assignments->count() }}</div>
    <div class="panel"><strong>Students in scope</strong><br><a href="{{ route('teacher.students.index') }}">View students</a></div>
</div>
<h2>Assignments</h2>
@foreach ($assignments as $assignment)
    <article class="panel"><strong>{{ $assignment->schoolClass->name }} {{ $assignment->section->name }}</strong> · {{ $assignment->subject->name }}<br><a href="{{ route('teacher.attendance.edit', $assignment) }}">Attendance</a> · <a href="{{ route('teacher.marks.edit', $assignment) }}">Marks</a> · <a href="{{ route('teacher.results.index', $assignment) }}">Results</a></article>
@endforeach
<h2>Notices</h2>
@forelse ($notices as $notice)<article class="panel"><strong>{{ $notice->title }}</strong><p>{{ $notice->body }}</p></article>@empty<p>No notices.</p>@endforelse
@endsection