<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\PersonalAccessToken;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'string', 'max:200'],
        ]);

        $user = User::query()->where('email', $validated['email'])->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            Log::notice('auth.login.failed', [
                'event_id' => (string) Str::uuid(),
            ]);

            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if (! in_array($user->role, ['student', 'enrollment_staff'], true)) {
            Log::warning('auth.login.role_rejected', [
                'user_id' => $user->id,
                'role' => $user->role,
            ]);

            throw ValidationException::withMessages([
                'email' => ['This account is not enabled for the enrollment service.'],
            ]);
        }

        $expiresAt = now()->addMinutes(config('sanctum.expiration', 60));
        $token = $user->createToken('flutter-web', ['*'], $expiresAt);

        Log::info('auth.login.succeeded', [
            'user_id' => $user->id,
            'role' => $user->role,
        ]);

        return response()->json([
            'access_token' => $token->plainTextToken,
            'token_type' => 'Bearer',
            'expires_at' => $expiresAt->toIso8601String(),
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'role' => $user->role,
            ],
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'role' => $user->role,
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $token = $request->user()->currentAccessToken();

        if ($token instanceof PersonalAccessToken) {
            $token->delete();
        }

        Log::info('auth.logout.succeeded', [
            'user_id' => $request->user()->id,
        ]);

        return response()->json(['message' => 'Signed out.']);
    }
}
