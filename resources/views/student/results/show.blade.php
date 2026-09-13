@extends('student.layout')
@section('title', $examResult->exam->name)
@section('content')
    <h1>{{ $examResult->exam->name }}</h1>
    <div class="grid"><div class="panel"><strong>Total</strong><br>{{ $result->total }} / {{ $result->maximum }}</div><div class="panel"><strong>Percentage</strong><br>{{ $result->percentage }}%</div><div class="panel"><strong>Grade</strong><br>{{ $result->grade }}</div><div class="panel"><strong>Outcome</strong><br>{{ $result->passed ? 'Passed' : 'Not passed' }}</div></div>
    @if ($examResult->overall_remarks)<p><strong>Remarks:</strong> {{ $examResult->overall_remarks }}</p>@endif
@endsection