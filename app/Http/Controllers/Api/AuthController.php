<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\Verified;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @param  \PHPOpenSourceSaver\JWTAuth\JWTGuard  $guard
     * @return void
     */
    public function __construct(protected Guard $guard)
    {
    }

    public function loginAction(LoginRequest $request): JsonResponse
    {
        if (config('auth.lowercase_usernames')) {
            $request->merge(['email' => Str::lower($request->email)]);
        }

        if (! $token = $this->guard->attempt($request->only(['email', 'password']))) {
            $this->throwFailedAuthenticationException($request);
        }

        return $this->respondWithToken($token);
    }

    public function userAction(Request $request): ?User
    {
        return $request->user();
    }

    /**
     * Destroy an authenticated session.
     */
    public function logoutAction(): JsonResponse
    {
        $this->guard->logout();

        return response()->json(['message' => 'Logged out']);
    }

    public function registerAction(RegisterRequest $request)
    {
        if (config('auth.lowercase_usernames')) {
            $request->merge([
                'email' => Str::lower($request->email),
            ]);
        }

        event(new Registered($user = User::create($request->validated())));

        $token = $this->guard->login($user);

        return $this->respondWithToken($token, 201);
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
        // $this->limiter->increment($request);

        throw ValidationException::withMessages([
            'email' => [trans('auth.failed')],
        ]);
    }

    protected function respondWithToken(string $token, int $status = 200): JsonResponse
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => $this->guard->factory()->getTTL() * 60,
        ], $status);
    }
}
