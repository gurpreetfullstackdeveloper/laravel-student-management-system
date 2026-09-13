@extends('student.layout')
@section('title', 'Results')
@section('content')
    <h1>Published Results</h1>
    <table><thead><tr><th>Exam</th><th>Type</th><th>Academic year</th><th>View</th></tr></thead><tbody>
    @forelse ($results as $examResult)
        <tr><td>{{ $examResult->exam->name }}</td><td>{{ ucfirst(str_replace('_', ' ', $examResult->exam->type)) }}</td><td>{{ $examResult->exam->academicYear->name }}</td><td><a href="{{ route('student.results.show', $examResult) }}">View result</a></td></tr>
    @empty <tr><td colspan="4">No published results.</td></tr> @endforelse
    </tbody></table>
    {{ $results->links() }}
@endsection