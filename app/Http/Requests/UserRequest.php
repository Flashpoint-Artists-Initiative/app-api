<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Http\Requests\Auth\RegisterRequest;
use Orion\Http\Requests\Request;

class UserRequest extends Request
{
    public function commonRules(): array
    {
        return [
            'legal_name' => ['string', 'nullable'],
            'preferred_name' => ['string', 'nullable'],
            'birthday' => ['date', 'nullable'],
            'email' => ['email', 'nullable'],
            'password' => ['string', 'nullable'],
        ];
    }

    public function storeRules(): array
    {
        $request = new RegisterRequest();

        return $request->rules();
    }
}
