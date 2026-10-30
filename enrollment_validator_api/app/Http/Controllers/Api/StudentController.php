<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    // Display all students
    public function index()
    {
        return response()->json(Student::all());
    }

    // Add a new student
    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_number' => 'required|string|max:255|unique:students',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'nullable|email',
            'course' => 'nullable|string|max:255',
            'year_level' => 'nullable|string|max:255',
            'section' => 'nullable|string|max:255',
        ]);

        $student = Student::create($validated);

        return response()->json([
            'message' => 'Student created successfully.',
            'data' => $student
        ], 201);
    }

    // Display one student
    public function show(string $id)
    {
        $student = Student::findOrFail($id);

        return response()->json($student);
    }

    // Update a student
    public function update(Request $request, string $id)
    {
        $student = Student::findOrFail($id);

        $validated = $request->validate([
            'student_number' => 'sometimes|required|string|max:255|unique:students,student_number,' . $id,
            'first_name' => 'sometimes|required|string|max:255',
            'last_name' => 'sometimes|required|string|max:255',
            'email' => 'nullable|email',
            'course' => 'nullable|string|max:255',
            'year_level' => 'nullable|string|max:255',
            'section' => 'nullable|string|max:255',
        ]);

        $student->update($validated);

        return response()->json([
            'message' => 'Student updated successfully.',
            'data' => $student
        ]);
    }

    // Delete a student
public function destroy(string $id)
{
    $student = Student::findOrFail($id);
    $student->delete();

    return response()->json([
        'message' => 'Student deleted successfully.'
    ]);
}
}
