<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Requirement;
use Illuminate\Http\Request;

class RequirementController extends Controller
{
    // Display all requirements
    public function index()
    {
        return response()->json(
            Requirement::with('student')->get()
        );
    }

    // Add a requirement
    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'requirement_name' => 'required|string|max:255',
            'status' => 'sometimes|in:Pending,Submitted,Approved,Rejected',
            'remarks' => 'nullable|string',
        ]);

        $requirement = Requirement::create($validated);

        return response()->json([
            'message' => 'Requirement added successfully.',
            'data' => $requirement
        ], 201);
    }

    // Display one requirement
    public function show(Requirement $requirement)
    {
        return response()->json($requirement->load('student'));
    }

    // Update a requirement
    public function update(Request $request, Requirement $requirement)
    {
        $validated = $request->validate([
            'student_id' => 'sometimes|exists:students,id',
            'requirement_name' => 'sometimes|string|max:255',
            'status' => 'sometimes|in:Pending,Submitted,Approved,Rejected',
            'remarks' => 'nullable|string',
        ]);

        $requirement->update($validated);

        return response()->json([
            'message' => 'Requirement updated successfully.',
            'data' => $requirement
        ]);
    }

    // Delete a requirement
    public function destroy(Requirement $requirement)
    {
        $requirement->delete();

        return response()->json([
            'message' => 'Requirement deleted successfully.'
        ]);
    }
}