<?php

declare(strict_types=1);

namespace App\Models\Ticketing;

use App\Models\Event;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Waiver extends Model
{
    use HasFactory;

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function completedWaivers(): HasMany
    {
        return $this->hasMany(CompletedWaiver::class);
    }
}
