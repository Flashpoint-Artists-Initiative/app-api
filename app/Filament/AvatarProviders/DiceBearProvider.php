<?php

declare(strict_types=1);

namespace App\Filament\AvatarProviders;

use App\Models\User;
use Filament\AvatarProviders\Contracts;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class DiceBearProvider implements Contracts\AvatarProvider
{
    /** @param User $record */
    public function get(Model|Authenticatable $record): string
    {
        $hash = md5("{$record->id}-{$record->email}");

        return 'https://api.dicebear.com/9.x/thumbs/svg?randomizeIds=true&seed=' . $hash;
    }
}
