@extends('student.layout')
@section('title', 'Fees')
@section('content')
    <h1>Fees</h1>
    <table><thead><tr><th>Fee</th><th>Academic year</th><th>Payable</th><th>Paid</th><th>Outstanding</th><th></th></tr></thead><tbody>
    @forelse ($fees as $fee)
        <tr><td>{{ $fee->feeStructureItem->feeType->name }}</td><td>{{ $fee->academicYear->name }}</td><td>{{ number_format($fee->amount_payable, 2) }}</td><td>{{ number_format($fee->paid_amount, 2) }}</td><td>{{ number_format($fee->outstanding_amount, 2) }}</td><td><a href="{{ route('student.fees.show', $fee) }}">History</a></td></tr>
    @empty <tr><td colspan="6">No fee records.</td></tr> @endforelse
    </tbody></table>
    {{ $fees->links() }}
@endsection