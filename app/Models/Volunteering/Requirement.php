<?php

declare(strict_types=1);

namespace App\Models\Volunteering;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Requirement extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'icon',
        'description',
    ];

    // public function shifts(): BelongsToMany
    // {
    //     return $this->belongsToMany(ShiftType::class, 'shift_type_requirements')->withTimestamps();
    // }
}
