<?php

declare(strict_types=1);

namespace App\Http\Controllers\Fortify;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Http\Requests\LoginRequest;
use Laravel\Fortify\LoginRateLimiter;

class AuthenticationController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @param  \PHPOpenSourceSaver\JWTAuth\JWTGuard  $guard
     * @return void
     */
    public function __construct(protected Guard $guard, protected LoginRateLimiter $limiter)
    {
    }

    /**
     * Attempt to authenticate a new session.
     *
     * @return mixed
     */
    public function store(LoginRequest $request)
    {
        if (config('fortify.lowercase_usernames')) {
            $request->merge(['email' => Str::lower($request->email)]);
        }

        if (! $token = $this->guard->attempt($request->only(['email', 'password']))) {
            $this->throwFailedAuthenticationException($request);
        }

        return $this->respondWithToken($token);
    }

    /**
     * Get the currently logged in user
     */
    public function me(Request $request): ?User
    {
        return $request->user();
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(): JsonResponse
    {
        $this->guard->logout();

        return response()->json(['message' => 'Logged out']);
    }

    /**
     * Mark the authenticated user's email address as verified.
     *
     * @param  \Laravel\Fortify\Http\Requests\VerifyEmailRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function verifyEmail(FormRequest $request)
    {
        $user = User::find($request->route('id'));

        if (! $user || ! hash_equals(sha1($user->getEmailForVerification()), (string) $request->route('hash'))) {
            throw ValidationException::withMessages(['email' => 'Invalid verification link']);
        }

        if ($user->hasVerifiedEmail()) {
            return response()->json(['message' => 'Email already verified'], 400);
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        return response()->json(['message' => 'Your email has been verified'], 204);
    }

    /**
     * Throw a failed authentication validation exception.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function throwFailedAuthenticationException($request)
    {
        $this->limiter->increment($request);

        throw ValidationException::withMessages([
            'email' => [trans('auth.failed')],
        ]);
    }

    protected function respondWithToken(string $token): JsonResponse
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => $this->guard->factory()->getTTL() * 60,
        ]);
    }
}
