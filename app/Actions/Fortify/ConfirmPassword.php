<?php

namespace App\Actions\Fortify;

use Illuminate\Contracts\Auth\Guard;
use Laravel\Fortify\Fortify;

class ConfirmPassword
{
    /**
     * Confirm that the given password is valid for the given user.
     *
     * @param  \Illuminate\Contracts\Auth\Guard  $guard
     * @param  mixed  $user
     * @param  string|null  $password
     * @return bool
     */
    public function __invoke(Guard $guard, $user, ?string $password = null)
    {
        $username = Fortify::username();

        return $guard->validate([
            $username => $user->{$username},
            'password' => $password,
        ]);
    }
}