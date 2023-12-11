<?php

namespace App\Http\Controllers\Fortify;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Foundation\Http\FormRequest;
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
            return $this->throwFailedAuthenticationException($request);
        }

        return $this->respondWithToken($token);
    }

    public function me(Request $request)
    {
        return $request->user();
    }

    /**
     * Destroy an authenticated session.
     *
     * @param  \Illuminate\Http\Request  $request
     */
    public function destroy()
    {
        $this->guard->logout();

        return response()->json(['message' => 'Logged out']);
    }

    /**
     * Mark the authenticated user's email address as verified.
     *
     * @param  \Laravel\Fortify\Http\Requests\VerifyEmailRequest  $request
     * @return \Laravel\Fortify\Contracts\VerifyEmailResponse
     */
    public function verifyEmail(FormRequest $request)
    {
        $user = User::find($request->route('id'));

        if (! $user) {
            throw ValidationException::withMessages(['user' => 'Unknown User']);
        }

        if (! hash_equals(sha1($user->getEmailForVerification()), (string) $request->route('hash'))) {
            throw ValidationException::withMessages(['email' => 'Invalid verification link']);
        }

        if ($user->hasVerifiedEmail()) {
            return response()->json(['message' => 'Email already verified']);
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        return response()->json(['message' => 'Your email has been verified']);
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

    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => $this->guard->factory()->getTTL() * 60,
        ]);
    }
}
