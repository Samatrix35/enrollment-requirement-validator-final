<?php

namespace Tests\Feature;

use App\Models\Requirement;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecureEnrollmentApiTest extends TestCase
{
    use RefreshDatabase;

    private Student $student;
    private Student $otherStudent;
    private User $studentUser;
    private User $staffUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->student = Student::create([
            'student_number' => 'TEST-1001',
            'first_name' => 'Sample',
            'last_name' => 'Student',
            'course' => 'BSIT',
            'year_level' => '3',
            'section' => 'A',
        ]);
        $this->otherStudent = Student::create([
            'student_number' => 'TEST-1002',
            'first_name' => 'Other',
            'last_name' => 'Student',
        ]);
        $this->studentUser = User::factory()->create([
            'name' => 'Sample Student',
            'email' => 'student@example.test',
            'password' => 'a-long-test-password',
            'role' => 'student',
            'student_id' => $this->student->id,
        ]);
        $this->staffUser = User::factory()->create([
            'name' => 'Enrollment Staff',
            'email' => 'staff@example.test',
            'password' => 'a-different-test-password',
            'role' => 'enrollment_staff',
            'student_id' => null,
        ]);
    }

    public function test_api_requires_authentication_and_staff_role_for_management_routes(): void
    {
        $this->getJson('/api/students')->assertUnauthorized();

        $this->actingAs($this->studentUser, 'sanctum')
            ->getJson('/api/students')
            ->assertForbidden();

        $this->actingAs($this->staffUser, 'sanctum')
            ->getJson('/api/students')
            ->assertOk();
    }

    public function test_student_can_only_view_and_submit_their_own_requirement(): void
    {
        $ownRequirement = Requirement::create([
            'student_id' => $this->student->id,
            'requirement_name' => 'Transcript',
            'status' => 'Pending',
        ]);
        $otherRequirement = Requirement::create([
            'student_id' => $this->otherStudent->id,
            'requirement_name' => 'Clearance',
            'status' => 'Pending',
        ]);

        $this->actingAs($this->studentUser, 'sanctum')
            ->getJson('/api/my/enrollment')
            ->assertOk()
            ->assertJsonPath('student.student_number', 'TEST-1001')
            ->assertJsonMissingPath('student.email');

        $this->actingAs($this->studentUser, 'sanctum')
            ->patchJson("/api/my/requirements/{$ownRequirement->id}/submit")
            ->assertOk()
            ->assertJsonPath('data.status', 'Submitted');

        $this->actingAs($this->studentUser, 'sanctum')
            ->patchJson("/api/my/requirements/{$otherRequirement->id}/submit")
            ->assertNotFound();

        $this->actingAs($this->studentUser, 'sanctum')
            ->patchJson("/api/requirements/{$ownRequirement->id}", ['status' => 'Approved'])
            ->assertForbidden();
    }

    public function test_staff_can_decide_only_submitted_requirements_and_invalid_values_are_rejected(): void
    {
        $requirement = Requirement::create([
            'student_id' => $this->student->id,
            'requirement_name' => 'Transcript',
            'status' => 'Submitted',
        ]);

        $this->actingAs($this->staffUser, 'sanctum')
            ->patchJson("/api/requirements/{$requirement->id}", ['status' => 'Approved'])
            ->assertOk()
            ->assertJsonPath('data.status', 'Approved');

        $this->actingAs($this->staffUser, 'sanctum')
            ->postJson('/api/requirements', [
                'student_id' => $this->student->id,
                'requirement_name' => 'Invalid status test',
                'status' => 'Approved',
            ])
            ->assertUnprocessable();

        $pending = Requirement::create([
            'student_id' => $this->student->id,
            'requirement_name' => 'Pending item',
            'status' => 'Pending',
        ]);

        $this->actingAs($this->staffUser, 'sanctum')
            ->patchJson("/api/requirements/{$pending->id}", ['status' => 'Approved'])
            ->assertStatus(409);
    }

    public function test_login_token_can_be_revoked_and_invalid_or_expired_tokens_are_rejected(): void
    {
        $this->postJson('/api/auth/login', [
            'email' => $this->studentUser->email,
            'password' => 'wrong-password',
        ])->assertUnprocessable();

        $login = $this->postJson('/api/auth/login', [
            'email' => $this->studentUser->email,
            'password' => 'a-long-test-password',
        ])->assertOk();

        $token = $login->json('access_token');
        $this->withToken($token)->getJson('/api/me')->assertOk();
        $this->app['auth']->forgetGuards();

        $parts = explode('|', $token, 2);
        $altered = $parts[0].'|'.substr($parts[1], 0, -1).($parts[1][-1] === 'a' ? 'b' : 'a');
        $this->withToken($altered)->getJson('/api/me')->assertUnauthorized();
        $this->app['auth']->forgetGuards();

        $expired = $this->studentUser->createToken(
            'expired-test', ['*'], now()->subMinute(),
        )->plainTextToken;
        $this->withToken($expired)->getJson('/api/me')->assertUnauthorized();
        $this->app['auth']->forgetGuards();

        $this->withToken($token)->postJson('/api/auth/logout')->assertOk();
        $this->app['auth']->forgetGuards();
        $this->withToken($token)->getJson('/api/me')->assertUnauthorized();
    }

    public function test_api_responses_include_safe_headers_and_no_store(): void
    {
        $response = $this->actingAs($this->staffUser, 'sanctum')->getJson('/api/students');

        $response->assertOk()
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'DENY')
            ->assertHeader('Referrer-Policy', 'no-referrer')
            ->assertHeader('Cache-Control', 'no-store, private');
    }
}
