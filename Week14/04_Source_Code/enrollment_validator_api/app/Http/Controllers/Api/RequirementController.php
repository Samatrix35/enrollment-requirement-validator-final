<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\RequirementResource;
use App\Models\Requirement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class RequirementController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'status' => ['sometimes', Rule::in(['Pending', 'Submitted', 'Approved', 'Rejected'])],
        ]);

        $query = Requirement::query()->with('student:id,student_number,first_name,last_name,course,year_level,section');

        if (isset($validated['status'])) {
            $query->where('status', $validated['status']);
        }

        return RequirementResource::collection($query->orderBy('id')->paginate(50));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_id' => ['required', 'integer', 'exists:students,id'],
            'requirement_name' => ['required', 'string', 'max:120'],
            'status' => ['prohibited'],
            'remarks' => ['prohibited'],
        ]);

        $requirement = Requirement::create([
            'student_id' => $validated['student_id'],
            'requirement_name' => $validated['requirement_name'],
            'status' => 'Pending',
            'remarks' => null,
        ]);

        Log::info('enrollment.requirement.created', [
            'actor_id' => $request->user()->id,
            'requirement_id' => $requirement->id,
            'student_id' => $requirement->student_id,
        ]);

        return (new RequirementResource($requirement->load('student')))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Requirement $requirement)
    {
        return new RequirementResource($requirement->load('student'));
    }

    public function update(Request $request, Requirement $requirement)
    {
        $validated = $request->validate([
            'requirement_name' => ['sometimes', 'required', 'string', 'max:120'],
            'status' => ['sometimes', 'required', Rule::in(['Approved', 'Rejected'])],
            'remarks' => ['sometimes', 'nullable', 'string', 'max:500'],
            'student_id' => ['prohibited'],
        ]);

        if (isset($validated['status'])) {
            if ($requirement->status !== 'Submitted') {
                return response()->json([
                    'message' => 'Only submitted requirements can receive a staff decision.',
                ], 409);
            }

            if ($validated['status'] === 'Rejected' && blank($validated['remarks'] ?? null)) {
                return response()->json([
                    'message' => 'Add a brief correction note when returning a requirement.',
                    'errors' => ['remarks' => ['A correction note is required.']],
                ], 422);
            }
        }

        $requirement->update($validated);

        Log::info('enrollment.requirement.updated', [
            'actor_id' => $request->user()->id,
            'requirement_id' => $requirement->id,
            'status' => $requirement->status,
        ]);

        return new RequirementResource($requirement->refresh()->load('student'));
    }

    public function destroy(Request $request, Requirement $requirement)
    {
        $requirementId = $requirement->id;
        $studentId = $requirement->student_id;
        $requirement->delete();

        Log::notice('enrollment.requirement.deleted', [
            'actor_id' => $request->user()->id,
            'requirement_id' => $requirementId,
            'student_id' => $studentId,
        ]);

        return response()->json(['message' => 'Requirement record deleted.']);
    }
}
