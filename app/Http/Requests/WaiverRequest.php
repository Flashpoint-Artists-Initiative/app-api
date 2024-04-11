<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Orion\Http\Requests\Request;

class WaiverRequest extends Request
{
    /**
     * @return array<string, string[]>
     */
    public function commonRules(): array
    {
        return [
            'title' => ['string'],
            'content' => ['string'],
            'minor_waiver' => ['boolean'],
        ];
    }

    /**
     * @return array<string, string[]>
     */
    public function storeRules(): array
    {
        return [
            'title' => ['string', 'required'],
            'content' => ['required', 'string'],
        ];
    }
}
