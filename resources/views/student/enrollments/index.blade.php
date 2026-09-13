@extends('student.layout')
@section('title', 'Enrollment Information')
@section('content')
    <h1>Enrollment Information</h1>
    <table><thead><tr><th>Academic year</th><th>Class</th><th>Section</th><th>Roll no.</th><th>Status</th></tr></thead><tbody>
    @forelse ($enrollments as $enrollment)
        <tr><td>{{ $enrollment->academicYear->name }}</td><td>{{ $enrollment->schoolClass->name }}</td><td>{{ $enrollment->section->name }}</td><td>{{ $enrollment->roll_no }}</td><td>{{ ucfirst($enrollment->status) }}</td></tr>
    @empty <tr><td colspan="5">No enrollment records.</td></tr> @endforelse
    </tbody></table>
    {{ $enrollments->links() }}
@endsection