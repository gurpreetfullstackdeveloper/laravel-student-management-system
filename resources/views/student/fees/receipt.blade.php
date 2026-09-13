<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Receipt {{ $feePayment->receipt_no }}</title></head>
<body>
    <main>
        <h1>Fee Payment Receipt</h1>
        <p><strong>Receipt:</strong> {{ $feePayment->receipt_no }}</p>
        <p><strong>Student:</strong> {{ $feePayment->studentFee->student->user->name }}</p>
        <p><strong>Admission number:</strong> {{ $feePayment->studentFee->student->admission_no }}</p>
        <p><strong>Fee:</strong> {{ $feePayment->studentFee->feeStructureItem->feeType->name }}</p>
        <p><strong>Academic year:</strong> {{ $feePayment->studentFee->academicYear->name }}</p>
        <p><strong>Paid at:</strong> {{ $feePayment->paid_at->format('Y-m-d H:i') }}</p>
        <p><strong>Method:</strong> {{ ucfirst(str_replace('_', ' ', $feePayment->payment_method)) }}</p>
        <p><strong>Amount paid:</strong> {{ number_format($feePayment->amount_paid, 2) }}</p>
        <button type="button" onclick="window.print()">Print receipt</button>
    </main>
</body>
</html>