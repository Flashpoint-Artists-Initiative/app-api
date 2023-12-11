<?php

declare(strict_types=1);

/**
 * Extended Fortify controller to allow for the JWT auth guard
 */

namespace App\Http\Controllers\Fortify;

use Illuminate\Contracts\Auth\Guard;
use Laravel\Fortify\Http\Controllers\NewPasswordController as VendorController;

class NewPasswordController extends VendorController
{
    /**
     * Create a new controller instance.
     *
     * @param  \Illuminate\Contracts\Auth\StatefulGuard  $guard
     * @return void
     */
    public function __construct(Guard $guard)
    {
        $this->guard = $guard;
    }
}
