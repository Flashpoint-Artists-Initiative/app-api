<?php

declare(strict_types=1);

namespace App\Models\Ticketing;

use App\Models\Event;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as ContractsAuditable;

class Waiver extends Model implements ContractsAuditable
{
    use Auditable, HasFactory;

    protected $fillable = [
        'event_id',
        'title',
        'content',
        'minor_waiver',
    ];

    /**
     * @return BelongsTo<Event, $this>
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * @return HasMany<CompletedWaiver, $this>
     */
    public function completedWaivers(): HasMany
    {
        return $this->hasMany(CompletedWaiver::class);
    }
}
