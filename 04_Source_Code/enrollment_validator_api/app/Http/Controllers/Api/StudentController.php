<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\StudentResource;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class StudentController extends Controller
{
    public function index()
    {
        return StudentResource::collection(
            Student::query()->orderBy('last_name')->orderBy('first_name')->paginate(50),
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_number' => ['required', 'string', 'max:40', 'alpha_dash', 'unique:students,student_number'],
            'first_name' => ['required', 'string', 'max:80'],
            'last_name' => ['required', 'string', 'max:80'],
            'email' => ['nullable', 'email', 'max:255'],
            'course' => ['nullable', 'string', 'max:120'],
            'year_level' => ['nullable', 'string', 'max:32'],
            'section' => ['nullable', 'string', 'max:32'],
        ]);

        $student = Student::create($validated);

        Log::info('enrollment.student.created', [
            'actor_id' => $request->user()->id,
            'student_id' => $student->id,
        ]);

        return (new StudentResource($student))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Student $student)
    {
        return new StudentResource($student);
    }

    public function update(Request $request, Student $student)
    {
        $validated = $request->validate([
            'student_number' => [
                'sometimes', 'required', 'string', 'max:40', 'alpha_dash',
                Rule::unique('students', 'student_number')->ignore($student->id),
            ],
            'first_name' => ['sometimes', 'required', 'string', 'max:80'],
            'last_name' => ['sometimes', 'required', 'string', 'max:80'],
            'email' => ['sometimes', 'nullable', 'email', 'max:255'],
            'course' => ['sometimes', 'nullable', 'string', 'max:120'],
            'year_level' => ['sometimes', 'nullable', 'string', 'max:32'],
            'section' => ['sometimes', 'nullable', 'string', 'max:32'],
        ]);

        $student->update($validated);

        Log::info('enrollment.student.updated', [
            'actor_id' => $request->user()->id,
            'student_id' => $student->id,
        ]);

        return new StudentResource($student->refresh());
    }

    public function destroy(Request $request, Student $student)
    {
        $studentId = $student->id;
        $student->delete();

        Log::notice('enrollment.student.deleted', [
            'actor_id' => $request->user()->id,
            'student_id' => $studentId,
        ]);

        return response()->json(['message' => 'Student record deleted.']);
    }
}
