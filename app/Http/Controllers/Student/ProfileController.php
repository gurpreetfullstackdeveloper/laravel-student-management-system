<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\Student\UpdateProfileRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(): View
    {
        $student = auth()->user()->student;
        abort_unless($student, 404);
        $this->authorize('view', $student);

        return view('student.profile.edit', compact('student'));
    }

    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        $student = $request->user()->student;
        abort_unless($student, 404);
        $this->authorize('update', $student);
        $student->update($request->validated());

        return to_route('student.profile.edit')->with('status', 'Profile updated.');
    }
}