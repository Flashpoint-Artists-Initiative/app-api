<?php
declare(strict_types=1);

namespace App\Filament\AvatarProviders;

use Filament\AvatarProviders\Contracts;
use Filament\Facades\Filament;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class DiceBearProvider implements Contracts\AvatarProvider
{
    /** @param User $record */
    public function get(Model | Authenticatable $record): string
    {   
        $hash = md5("{$record->id}-{$record->email}");
 
        return 'https://api.dicebear.com/9.x/thumbs/svg?randomizeIds=true&seed=' . $hash;
    }
}
