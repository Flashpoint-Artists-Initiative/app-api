<?php

declare(strict_types=1);

namespace App\Actions\Fortify;

use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Support\Str;

class CompletePasswordReset
{
    /**
     * Complete the password reset process for the given user.
     *
     * @param  \Illuminate\Contracts\Auth\StatefulGuard  $guard
     * @param  mixed  $user
     * @return void
     */
    public function __invoke(Guard $guard, $user)
    {
        $user->setRememberToken(Str::random(60));

        $user->save();

        event(new PasswordReset($user));
    }
}
