<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

class HealthController extends Controller
{
    private function release(): string
    {
        $release = (string) config('enrollment.release', 'unknown');
        return preg_match('/\A[a-zA-Z0-9._-]{1,40}\z/', $release) ? $release : 'unknown';
    }

    public function live(): JsonResponse
    {
        return response()->json(['status' => 'alive', 'release' => $this->release()]);
    }

    public function ready(): JsonResponse
    {
        try {
            DB::select('select 1');
            $schemaReady = Schema::hasColumns('requirements', ['student_id', 'requirement_name', 'status'])
                && Schema::hasColumns('personal_access_tokens', ['token', 'expires_at'])
                && Schema::hasColumns('students', ['id', 'student_number'])
                && Schema::hasColumns('users', ['role', 'student_id']);
            return response()->json([
                'status' => $schemaReady ? 'ready' : 'not_ready',
                'release' => $this->release(),
                'checks' => ['database' => 'up', 'schema' => $schemaReady ? 'up' : 'down'],
            ], $schemaReady ? 200 : 503);
        } catch (Throwable $exception) {
            // Public readiness signals never disclose paths, credentials, or exceptions.
            return response()->json([
                'status' => 'not_ready', 'release' => $this->release(),
                'checks' => ['database' => 'down', 'schema' => 'unknown'],
            ], 503);
        }
    }
}
