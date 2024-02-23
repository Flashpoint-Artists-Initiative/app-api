<?php

declare(strict_types=1);

namespace App\Models\Volunteering;

use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Team extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'name',
        'description',
        'email',
        'active',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function shiftTypes(): HasMany
    {
        return $this->hasMany(ShiftType::class);
    }

    public function shifts(): HasManyThrough
    {
        return $this->hasManyThrough(ShiftType::class, Shift::class);
    }

    public function volunteers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'volunteer_data');
    }
}
