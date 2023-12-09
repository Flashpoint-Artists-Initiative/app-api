<?php

namespace App\Http\Controllers\Fortify;

use App\Actions\Fortify\ConfirmPassword;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;
use Laravel\Fortify\Contracts\FailedPasswordConfirmationResponse;
use Laravel\Fortify\Contracts\PasswordConfirmedResponse;

class ConfirmablePasswordController
{
    /**
     * Create a new controller instance.
     *
     * @param  \Illuminate\Contracts\Auth\StatefulGuard  $guard
     * @return void
     */
    public function __construct(protected Guard $guard)
    {
    }

    /**
     * Show the confirm password view.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Laravel\Fortify\Contracts\ConfirmPasswordViewResponse
     */
    public function show(Request $request)
    {
        return app(ConfirmPasswordViewResponse::class);
    }

    /**
     * Confirm the user's password.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\Support\Responsable
     */
    public function store(Request $request)
    {
        $confirmed = app(ConfirmPassword::class)(
            $this->guard, $request->user(), $request->input('password')
        );

        return $confirmed
                    ? app(PasswordConfirmedResponse::class)
                    : app(FailedPasswordConfirmationResponse::class);
    }
}