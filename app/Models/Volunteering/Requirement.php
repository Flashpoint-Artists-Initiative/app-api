<?php

declare(strict_types=1);

namespace App\Models\Volunteering;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Requirement extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'icon',
        'description',
    ];

    public function shifts(): BelongsToMany
    {
        return $this->belongsToMany(Shift::class, 'shift_requirements')->withTimestamps();
    }
}
