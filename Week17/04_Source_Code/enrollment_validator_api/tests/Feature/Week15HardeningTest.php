<?php

namespace Tests\Feature;

use App\Models\{Requirement, Student, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class Week15HardeningTest extends TestCase
{
    use RefreshDatabase;

    private Student $student;
    private User $staff;
    private User $owner;

    protected function setUp(): void
    {
        parent::setUp();
        $this->student = Student::create(['student_number'=>'W15-001','first_name'=>'Demo','last_name'=>'Learner']);
        $this->staff = User::factory()->create(['role'=>'enrollment_staff','student_id'=>null]);
        $this->owner = User::factory()->create(['role'=>'student','student_id'=>$this->student->id]);
    }

    private function item(string $status = 'Pending'): Requirement
    {
        return Requirement::create(['student_id'=>$this->student->id,'requirement_name'=>'Transcript','status'=>$status]);
    }

    public function test_w15_01_duplicate_requirement_is_rejected_without_creating_an_extra_row(): void
    {
        $this->item();
        $this->actingAs($this->staff,'sanctum')->postJson('/api/requirements',[
            'student_id'=>$this->student->id,'requirement_name'=>'Transcript',
        ])->assertUnprocessable()->assertJsonValidationErrors('requirement_name');
        $this->assertDatabaseCount('requirements',1);
    }

    public function test_w15_02_requirement_name_boundary_120_allowed_121_rejected(): void
    {
        $this->actingAs($this->staff,'sanctum')->postJson('/api/requirements',[
            'student_id'=>$this->student->id,'requirement_name'=>str_repeat('A',120),
        ])->assertCreated();
        $this->postJson('/api/requirements',[
            'student_id'=>$this->student->id,'requirement_name'=>str_repeat('B',121),
        ])->assertUnprocessable();
    }

    public function test_w15_03_correction_note_boundary_500_allowed_501_rejected(): void
    {
        $item=$this->item('Submitted');
        $this->actingAs($this->staff,'sanctum')->patchJson('/api/requirements/'.$item->id,[
            'status'=>'Rejected','remarks'=>str_repeat('x',501),
        ])->assertUnprocessable();
        $this->assertSame('Submitted',$item->fresh()->status);
        $this->patchJson('/api/requirements/'.$item->id,[
            'status'=>'Rejected','remarks'=>str_repeat('x',500),
        ])->assertOk();
    }

    public function test_w15_04_complete_correction_and_approval_path(): void
    {
        $item=$this->item();
        $this->actingAs($this->owner,'sanctum')->patchJson('/api/my/requirements/'.$item->id.'/submit')->assertOk();
        $this->actingAs($this->staff,'sanctum')->patchJson('/api/requirements/'.$item->id,['status'=>'Rejected','remarks'=>'Use a clearer demo reference.'])->assertOk();
        $this->actingAs($this->owner,'sanctum')->getJson('/api/my/enrollment')->assertJsonPath('requirements.0.feedback','Use a clearer demo reference.');
        $this->patchJson('/api/my/requirements/'.$item->id.'/submit')->assertOk();
        $this->assertNull($item->fresh()->remarks);
        $this->actingAs($this->staff,'sanctum')->patchJson('/api/requirements/'.$item->id,['status'=>'Approved'])->assertOk();
        $this->actingAs($this->owner,'sanctum')->patchJson('/api/my/requirements/'.$item->id.'/submit')->assertStatus(409);
    }

    public function test_w15_05_repeat_staff_decision_cannot_overwrite_an_approved_item(): void
    {
        $item=$this->item('Submitted');
        $this->actingAs($this->staff,'sanctum')->patchJson('/api/requirements/'.$item->id,['status'=>'Approved'])->assertOk();
        $this->patchJson('/api/requirements/'.$item->id,['status'=>'Rejected','remarks'=>'Stale decision'])->assertStatus(409);
        $this->assertSame('Approved',$item->fresh()->status);
    }

    public function test_w15_06_ownership_cannot_be_reassigned_through_update(): void
    {
        $item=$this->item();
        $this->actingAs($this->staff,'sanctum')->patchJson('/api/requirements/'.$item->id,['student_id'=>999])->assertUnprocessable();
        $this->assertSame($this->student->id,$item->fresh()->student_id);
    }

    public function test_w15_07_whitespace_correction_note_is_rejected(): void
    {
        $item=$this->item('Submitted');
        $this->actingAs($this->staff,'sanctum')->patchJson('/api/requirements/'.$item->id,['status'=>'Rejected','remarks'=>'   '])->assertUnprocessable();
        $this->assertSame('Submitted',$item->fresh()->status);
    }

    public function test_w15_08_staff_queue_pagination_preserves_the_51st_item(): void
    {
        for($i=1;$i<=51;$i++) Requirement::create(['student_id'=>$this->student->id,'requirement_name'=>'Item '.$i,'status'=>'Submitted']);
        $this->actingAs($this->staff,'sanctum')->getJson('/api/requirements?status=Submitted')->assertJsonCount(50,'data')->assertJsonPath('meta.last_page',2);
        $this->getJson('/api/requirements?status=Submitted&page=2')->assertJsonCount(1,'data');
    }

    public function test_w15_09_delete_student_cascades_requirements(): void
    {
        $this->item();
        $this->actingAs($this->staff,'sanctum')->deleteJson('/api/students/'.$this->student->id)->assertOk();
        $this->assertDatabaseCount('requirements',0);
    }

    public function test_w15_10_nonexistent_student_requirement_rejected(): void
    {
        $this->actingAs($this->staff,'sanctum')->postJson('/api/requirements',['student_id'=>99999,'requirement_name'=>'Transcript'])->assertUnprocessable();
    }

    public function test_w15_11_student_cannot_create_update_or_delete_students(): void
    {
        $this->actingAs($this->owner,'sanctum')->postJson('/api/students',[])->assertForbidden();
        $this->patchJson('/api/students/'.$this->student->id,['first_name'=>'Changed'])->assertForbidden();
        $this->deleteJson('/api/students/'.$this->student->id)->assertForbidden();
    }

    public function test_w15_12_production_http_and_untrusted_forwarded_https_are_rejected(): void
    {
        $this->app->detectEnvironment(fn ()=>'production');
        $this->postJson('/api/auth/login',[])->assertStatus(400)->assertJsonPath('message','HTTPS is required.');
        $this->withHeader('X-Forwarded-Proto','https')->postJson('/api/auth/login',[])->assertStatus(400);
    }

    public function test_w15_13_secure_production_request_has_hsts(): void
    {
        $this->app->detectEnvironment(fn ()=>'production');
        $this->getJson('https://localhost/api/me')->assertUnauthorized()->assertHeader('Strict-Transport-Security','max-age=15552000');
    }

    public function test_w15_14_login_is_throttled_after_five_attempts(): void
    {
        for($i=0;$i<5;$i++) $this->postJson('/api/auth/login',['email'=>'missing@example.test','password'=>'bad'])->assertUnprocessable();
        $this->postJson('/api/auth/login',['email'=>'missing@example.test','password'=>'bad'])->assertStatus(429);
    }

    public function test_w15_15_login_failure_does_not_log_credentials(): void
    {
        Log::spy();
        $this->postJson('/api/auth/login',['email'=>'missing@example.test','password'=>'SensitiveTestPassword!'])->assertUnprocessable();
        Log::shouldHaveReceived('notice')->withArgs(fn($event,$context)=>$event==='auth.login.failed' && !str_contains(json_encode($context),'SensitiveTestPassword') && !str_contains(json_encode($context),'missing@example.test'))->once();
    }

    public function test_w15_16_student_without_link_gets_safe_not_found(): void
    {
        $this->owner->update(['student_id'=>null]);
        $this->actingAs($this->owner,'sanctum')->getJson('/api/my/enrollment')->assertNotFound()->assertJsonPath('message','Enrollment record not found.');
    }
}
