<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\RequirementResource;
use App\Http\Resources\StudentResource;
use App\Models\Requirement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MyEnrollmentController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $student = $request->user()->student;

        if (! $student) {
            return response()->json(['message' => 'Enrollment record not found.'], 404);
        }

        return response()->json([
            'student' => (new StudentResource($student))->resolve($request),
            'requirements' => RequirementResource::collection(
                $student->requirements()->orderBy('id')->get(),
            )->resolve($request),
        ]);
    }

    public function submit(Request $request, Requirement $requirement): JsonResponse
    {
        $student = $request->user()->student;

        if (! $student || $requirement->student_id !== $student->id) {
            return response()->json(['message' => 'Requirement not found.'], 404);
        }

        if (! in_array($requirement->status, ['Pending', 'Rejected'], true)) {
            return response()->json([
                'message' => 'Only pending or returned requirements can be resubmitted.',
            ], 409);
        }

        $updated = Requirement::query()->whereKey($requirement->id)
            ->where('student_id', $student->id)
            ->whereIn('status', ['Pending', 'Rejected'])
            ->update(['status' => 'Submitted', 'remarks' => null]);
        if ($updated !== 1) {
            return response()->json(['message' => 'The requirement changed. Refresh and try again.'], 409);
        }
        $requirement->refresh();

        Log::info('enrollment.requirement.submitted', [
            'user_id' => $request->user()->id,
            'requirement_id' => $requirement->id,
        ]);

        return response()->json([
            'message' => 'Requirement submitted for staff review.',
            'data' => (new RequirementResource($requirement))->resolve($request),
        ]);
    }
}
