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
            'legal_name' => 'string',
            'preferred_name' => 'string',
            'birthday' => 'date',
            'email' => 'email',
            'password' => 'string',
        ];
    }

    public function storeRules(): array
    {
        $request = new RegisterRequest();

        return $request->rules();
    }
}
