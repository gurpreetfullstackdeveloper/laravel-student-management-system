@extends('student.layout')
@section('title', 'Payment History')
@section('content')
    <h1>{{ $studentFee->feeStructureItem->feeType->name }} payment history</h1>
    <p>Outstanding: {{ number_format($studentFee->outstanding_amount, 2) }}</p>
    <table><thead><tr><th>Receipt</th><th>Date</th><th>Method</th><th>Amount</th></tr></thead><tbody>
    @forelse ($studentFee->payments as $payment)
        <tr><td>{{ $payment->receipt_no }}</td><td>{{ $payment->paid_at->format('Y-m-d') }}</td><td>{{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}</td><td>{{ number_format($payment->amount_paid, 2) }}</td><td><a href="{{ route('student.fee-payments.receipt', $payment) }}">Receipt</a></td></tr>
    @empty <tr><td colspan="5">No payments recorded.</td></tr> @endforelse
    </tbody></table>
@endsection