@extends('student.layout')
@section('title', 'My Profile')
@section('content')
    <h1>My Profile</h1>
    <p><strong>Name:</strong> {{ $student->user->name }}</p>
    <p><strong>Admission number:</strong> {{ $student->admission_no }}</p>
    <p><strong>Email:</strong> {{ $student->user->email ?? 'Not provided' }}</p>
    <form method="POST" action="{{ route('student.profile.update') }}">
        @csrf @method('PUT')
        <label for="guardian_phone">Guardian phone</label>
        <input id="guardian_phone" name="guardian_phone" value="{{ old('guardian_phone', $student->guardian_phone) }}">
        <label for="address">Address</label>
        <textarea id="address" name="address" rows="4">{{ old('address', $student->address) }}</textarea>
        <br><button type="submit">Save profile</button>
    </form>
@endsection