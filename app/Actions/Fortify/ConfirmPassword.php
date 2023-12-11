<?php

declare(strict_types=1);

namespace App\Actions\Fortify;

use Illuminate\Contracts\Auth\Guard;
use Laravel\Fortify\Fortify;

class ConfirmPassword
{
    /**
     * Confirm that the given password is valid for the given user.
     *
     * @param  mixed  $user
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
