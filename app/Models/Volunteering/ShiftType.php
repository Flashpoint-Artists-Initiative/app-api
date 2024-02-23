<?php

declare(strict_types=1);

namespace App\Models\Volunteering;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShiftType extends Model
{
    use HasFactory;

    protected $fillable = [
        'team_id',
        'title',
        'description',
        'length',
        'num_spots',
    ];

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function shifts(): HasMany
    {
        return $this->hasMany(Shift::class);
    }

    public function requirements(): BelongsToMany
    {
        return $this->belongsToMany(Requirement::class, 'shift_requirements')->withTimestamps();
    }

    public function volunteers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'volunteer_data');
    }
}
