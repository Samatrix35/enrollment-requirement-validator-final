<?php
// CLI-only fictional fixture builder. The evaluator supplies an isolated SQLite path.
require dirname(__DIR__).'/enrollment_validator_api/vendor/autoload.php';
$app=require dirname(__DIR__).'/enrollment_validator_api/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
if (! $app->environment(['local','testing']) || getenv('W16_FIXTURE_ALLOWED') !== 'yes') {
    throw new RuntimeException('Fixture creation requires local/testing and the explicit fixture flag.');
}
use App\Models\{Student,Requirement,User};
use Illuminate\Support\Facades\{DB,Hash};

$users=[];
DB::transaction(function () use (&$users) {
    $hash=Hash::make('FictionalBenchmark-Only!', ['rounds'=>4]);
    for($i=1;$i<=200;$i++) {
        $student=Student::create(['student_number'=>sprintf('W16-DEMO-%04d',$i),'first_name'=>'Fictional','last_name'=>'Learner '.$i,'course'=>'BSIT','year_level'=>'3','section'=>'A']);
        $user=User::create(['name'=>'Fictional Learner '.$i,'email'=>'w16-'.$i.'@example.test','password'=>$hash,'role'=>'student','student_id'=>$student->id]);
        $ids=[];
        foreach(['Birth Certificate','Transcript','Good Moral Certificate'] as $name) {
            $ids[]=Requirement::create(['student_id'=>$student->id,'requirement_name'=>$name,'status'=>'Pending'])->id;
        }
        $users[]=['student_id'=>$student->id,'requirement_id'=>$ids[0],'token'=>$user->createToken('week16-fixture',['*'],now()->addHours(6))->plainTextToken];
    }
    $staff=User::create(['name'=>'Fictional Enrollment Staff','email'=>'w16-staff@example.test','password'=>$hash,'role'=>'enrollment_staff','student_id'=>null]);
    $users[]=['staff_token'=>$staff->createToken('week16-fixture',['*'],now()->addHours(6))->plainTextToken];
});
// The caller stores this secret-bearing fixture only in the excluded runtime folder.
echo json_encode(['users'=>array_slice($users,0,200),'staff_token'=>$users[200]['staff_token'],'students'=>200,'requirements'=>600],JSON_THROW_ON_ERROR);
