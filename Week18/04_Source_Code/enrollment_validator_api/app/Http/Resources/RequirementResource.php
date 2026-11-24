<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RequirementResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $data = [
            'id' => $this->id,
            'requirement_name' => $this->requirement_name,
            'status' => $this->status,
        ];

        if ($this->status === 'Rejected' && $this->remarks) {
            $data['feedback'] = $this->remarks;
        }

        if ($request->user()?->role === 'enrollment_staff') {
            $data['remarks'] = $this->remarks;
            $data['student'] = $this->whenLoaded('student', fn () => [
                'id' => $this->student->id,
                'student_number' => $this->student->student_number,
                'first_name' => $this->student->first_name,
                'last_name' => $this->student->last_name,
                'course' => $this->student->course,
                'year_level' => $this->student->year_level,
                'section' => $this->student->section,
            ]);
        }

        return $data;
    }
}
