<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class Week16OperationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_liveness_is_public_minimal_and_not_cached(): void
    {
        $this->getJson('/api/health/live')->assertOk()->assertJsonStructure(['status','release'])
            ->assertJsonPath('status','alive')->assertHeader('Cache-Control','no-store, private')
            ->assertJsonMissingPath('database_path')->assertJsonMissingPath('environment');
    }

    public function test_readiness_checks_real_database_and_schema(): void
    {
        $this->getJson('/api/health/ready')->assertOk()->assertJsonPath('status','ready')->assertJsonPath('checks.schema','up');
    }

    public function test_missing_schema_is_not_ready(): void
    {
        Schema::drop('personal_access_tokens');
        $this->getJson('/api/health/ready')->assertStatus(503)->assertJsonPath('checks.schema','down');
    }

    public function test_unavailable_database_fails_safely_while_liveness_remains_up(): void
    {
        $original=DB::getDefaultConnection();
        config(['database.connections.broken'=>['driver'=>'sqlite','database'=>database_path('DOES-NOT-EXIST/private-secret.sqlite'),'prefix'=>'']]);
        DB::setDefaultConnection('broken');
        try {
            $this->getJson('/api/health/live')->assertOk();
            $response=$this->getJson('/api/health/ready')->assertStatus(503)->assertJsonPath('checks.database','down');
            $this->assertStringNotContainsString('private-secret',$response->getContent());
        } finally {
            DB::setDefaultConnection($original);
            DB::purge('broken');
        }
    }

    public function test_valid_correlation_id_is_returned_and_invalid_id_is_replaced(): void
    {
        $this->withHeader('X-Request-ID','week16-trace-001')->getJson('/api/health/live')->assertHeader('X-Request-ID','week16-trace-001')->assertHeader('Server-Timing');
        $response=$this->withHeader('X-Request-ID',str_repeat('x',81))->getJson('/api/health/live')->assertOk();
        $this->assertNotEquals(str_repeat('x',81),$response->headers->get('X-Request-ID'));
    }

    public function test_production_health_requires_https_and_release_is_minimized(): void
    {
        $this->app->detectEnvironment(fn ()=>'production');
        $this->getJson('/api/health/ready')->assertStatus(400);
        config(['enrollment.release'=>'secret/path/invalid']);
        $this->getJson('https://localhost/api/health/ready')->assertOk()->assertJsonPath('release','unknown');
    }

    public function test_operations_logging_excludes_body_token_and_query(): void
    {
        $request=\Illuminate\Http\Request::create('/api/health/live?private=DO-NOT-LOG','GET');
        $request->headers->set('Authorization','Bearer DO-NOT-LOG');
        $middleware=new \App\Http\Middleware\RecordRequestMetrics;
        \Illuminate\Support\Facades\Log::spy();
        $response=$middleware->handle($request,fn ()=>response()->json(['status'=>'alive']));
        $middleware->terminate($request,$response);
        \Illuminate\Support\Facades\Log::shouldHaveReceived('info')->withArgs(fn($event,$context)=>$event==='operations.request' && !str_contains(json_encode($context),'DO-NOT-LOG') && isset($context['duration_ms']))->once();
    }
}
