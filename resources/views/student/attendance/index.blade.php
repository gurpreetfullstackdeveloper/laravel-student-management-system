@extends('student.layout')
@section('title', 'Attendance')
@section('content')
    <h1>Attendance</h1>
    <form method="GET"><label for="month">Filter month</label><input id="month" name="month" type="month" value="{{ $month }}"><button type="submit">Filter</button></form>
    <table><thead><tr><th>Date</th><th>Class</th><th>Section</th><th>Status</th></tr></thead><tbody>
    @forelse ($attendance as $record)
        <tr><td>{{ $record->date->format('Y-m-d') }}</td><td>{{ $record->schoolClass->name }}</td><td>{{ $record->section->name }}</td><td>{{ ucfirst($record->status) }}</td></tr>
    @empty <tr><td colspan="4">No attendance records.</td></tr> @endforelse
    </tbody></table>
    {{ $attendance->links() }}
@endsection