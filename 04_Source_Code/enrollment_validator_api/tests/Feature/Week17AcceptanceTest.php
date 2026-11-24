<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Student;
use App\Models\Requirement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class Week17AcceptanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_empty_staff_update_returns_actionable_validation(): void
    {
        $staff = User::factory()->create(['role' => 'enrollment_staff']);
        $student = Student::create(['student_number'=>'W17-001','first_name'=>'Fictional','last_name'=>'Student']);
        $item = Requirement::create(['student_id'=>$student->id,'requirement_name'=>'Transcript','status'=>'Submitted']);
        Sanctum::actingAs($staff);
        $this->patchJson('/api/requirements/'.$item->id, [])->assertStatus(422)->assertJsonValidationErrors('update');
        $this->assertDatabaseHas('requirements', ['id'=>$item->id,'status'=>'Submitted']);
    }

    public function test_acceptance_loop_preserves_correction_and_reapproval(): void
    {
        $student = Student::create(['student_number'=>'W17-002','first_name'=>'Fictional','last_name'=>'Student']);
        $user=User::factory()->create(['role'=>'student','student_id'=>$student->id]);
        $staff=User::factory()->create(['role'=>'enrollment_staff']);
        $item=Requirement::create(['student_id'=>$student->id,'requirement_name'=>'Transcript','status'=>'Pending']);
        Sanctum::actingAs($user);
        $this->patchJson('/api/my/requirements/'.$item->id.'/submit')->assertOk()->assertJsonPath('data.status','Submitted');
        Sanctum::actingAs($staff);
        $this->patchJson('/api/requirements/'.$item->id,['status'=>'Rejected','remarks'=>'Check the fictional reference.'])->assertOk();
        Sanctum::actingAs($user);
        $this->getJson('/api/my/enrollment')->assertOk()->assertJsonPath('requirements.0.feedback','Check the fictional reference.');
        $this->patchJson('/api/my/requirements/'.$item->id.'/submit')->assertOk()->assertJsonMissingPath('data.feedback');
        Sanctum::actingAs($staff);
        $this->patchJson('/api/requirements/'.$item->id,['status'=>'Approved'])->assertOk();
        Sanctum::actingAs($user);
        $this->getJson('/api/my/enrollment')->assertJsonPath('requirements.0.status','Approved')->assertJsonMissingPath('requirements.0.remarks');
    }
}
