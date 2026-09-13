<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Requests\Teacher\MarksEntryRequest;
use App\Models\Exam;
use App\Models\Mark;
use App\Models\StudentEnrollment;
use App\Models\TeacherAssignment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class MarkController extends Controller
{
    public function edit(TeacherAssignment $assignment, Request $request): View
    {
        $this->authorize('view', $assignment);
        $exams = Exam::where('class_id', $assignment->class_id)->where('academic_year_id', $assignment->academic_year_id)->latest('start_date')->get();
        $exam = $this->examFor($assignment, $request->integer('exam_id'));
        $examSubject = $exam?->examSubjects()->where('subject_id', $assignment->subject_id)->first();
        $students = $this->studentsFor($assignment);
        $marks = $examSubject ? Mark::where('exam_subject_id', $examSubject->id)->whereIn('student_id', $students->pluck('id'))->get()->keyBy('student_id') : collect();
        return view('teacher.marks.edit', compact('assignment', 'exams', 'exam', 'examSubject', 'students', 'marks'));
    }

    public function update(MarksEntryRequest $request, TeacherAssignment $assignment): RedirectResponse
    {
        $this->authorize('view', $assignment);
        $exam = $this->examFor($assignment, $request->integer('exam_id'));
        abort_unless($exam, 404);
        $examSubject = $exam->examSubjects()->where('subject_id', $assignment->subject_id)->firstOrFail();
        $students = $this->studentsFor($assignment)->keyBy('id');
        DB::transaction(function () use ($request, $assignment, $examSubject, $students): void {
            foreach ($request->validated('marks') as $studentId => $value) {
                if ($value === null || ! $students->has($studentId)) continue;
                abort_if((float) $value > (float) $examSubject->max_marks, 422, 'Marks exceed the maximum.');
                $mark = Mark::firstOrNew(['exam_subject_id' => $examSubject->id, 'student_id' => $studentId]);
                $mark->fill(['entered_by' => $assignment->teacher_id, 'obtained_marks' => $value, 'remarks' => $request->input("remarks.$studentId")]);
                $this->authorize($mark->exists ? 'update' : 'create', $mark);
                $mark->save();
            }
        });
        return to_route('teacher.marks.edit', ['assignment' => $assignment, 'exam_id' => $exam->id])->with('status', 'Marks saved.');
    }

    private function examFor(TeacherAssignment $assignment, int $examId): ?Exam
    {
        return Exam::whereKey($examId)->where('class_id', $assignment->class_id)->where('academic_year_id', $assignment->academic_year_id)->first();
    }

    private function studentsFor(TeacherAssignment $assignment)
    {
        return StudentEnrollment::where('class_id', $assignment->class_id)->where('section_id', $assignment->section_id)->where('academic_year_id', $assignment->academic_year_id)->with('student.user')->get()->pluck('student');
    }
}