@extends('student.layout')

@section('title', 'Student Dashboard')

@section('content')
    <h1>Student Dashboard</h1>
    <p>Welcome, {{ $student->user->name }}.</p>
    <div class="grid">
        <div class="panel"><strong>Admission number</strong><br>{{ $student->admission_no }}</div>
        <div class="panel"><strong>Current enrollment</strong><br>{{ $enrollment?->schoolClass?->name ?? 'Not assigned' }} {{ $enrollment?->section?->name }}</div>
        <div class="panel"><strong>Attendance records</strong><br>{{ $attendanceCount }}</div>
        <div class="panel"><strong>Published results</strong><br>{{ $publishedResults }}</div>
        <div class="panel"><strong>Outstanding fees</strong><br>{{ number_format($outstandingFees, 2) }}</div>
    </div>
@endsection