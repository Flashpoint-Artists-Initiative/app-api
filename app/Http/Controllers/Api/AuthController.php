<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\Verified;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
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

    /**
     * Log a user in, returning a new JWT
     */
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

    /**
     * Return the currently logged in User
     */
    public function userAction(Request $request): ?User
    {
        return $request->user();
    }

    /**
     * Logout the authenticated token
     */
    public function logoutAction(): JsonResponse
    {
        $this->guard->logout();

        return response()->json(['message' => 'Logged out']);
    }

    /**
     * Register a new User, returning a new JWT on success
     */
    public function registerAction(RegisterRequest $request): JsonResponse
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
     * Send a password reset link to the submitted email
     */
    public function forgotPasswordAction(Request $request): JsonResponse
    {
        $request->validate(['email' => 'required|email']);

        // We will send the password reset link to this user. Once we have attempted
        // to send the link, we will examine the response then see the message we
        // need to show to the user. Finally, we'll send out a proper response.
        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status == Password::RESET_LINK_SENT) {
            return response()->json(['message' => __($status)], 200);
        }

        throw ValidationException::withMessages([
            'email' => [trans($status)],
        ]);
    }

    public function resetPasswordAction(ResetPasswordRequest $request): JsonResponse
    {
        $status = Password::reset($request->validated(), function (User $user, string $password) {
            $user->forceFill([
                'password' => Hash::make($password),
            ])->setRememberToken(Str::random(60));

            $user->save();

            event(new PasswordReset($user));
        });

        if ($status == Password::PASSWORD_RESET) {
            return response()->json(['message' => __($status)]);
        }

        $errorField = 'password';

        if ($status == Password::INVALID_USER) {
            $errorField = 'email';
        }

        if ($status == Password::INVALID_TOKEN) {
            $errorField = 'token';
        }

        throw ValidationException::withMessages([
            $errorField => [trans($status)],
        ]);
    }

    /**
     * Mark the authenticated user's email address as verified.
     */
    public function verifyEmail(FormRequest $request): JsonResponse
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
