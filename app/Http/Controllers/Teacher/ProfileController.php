<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Requests\Teacher\UpdateProfileRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(): View
    {
        $teacher = auth()->user()->teacher;
        abort_unless($teacher, 404);
        $this->authorize('view', $teacher);
        return view('teacher.profile.edit', compact('teacher'));
    }

    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        $teacher = $request->user()->teacher;
        abort_unless($teacher, 404);
        $this->authorize('update', $teacher);
        $teacher->update($request->validated());
        return to_route('teacher.profile.edit')->with('status', 'Profile updated.');
    }
}