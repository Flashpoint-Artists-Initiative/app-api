<?php

declare(strict_types=1);

namespace App\Models\Volunteering;

use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as ContractsAuditable;

/**
 * @property int $total_num_spots
 * @property float $percent_filled
 */
class Team extends Model implements ContractsAuditable
{
    use Auditable, HasFactory, SoftDeletes;

    protected $fillable = [
        'event_id',
        'name',
        'description',
        'email',
        'active',
    ];

    /** @var string[] */
    protected $withCount = [
        'volunteers',
    ];

    /**
     * @return BelongsTo<Event, Team>
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * @return HasMany<ShiftType>
     */
    public function shiftTypes(): HasMany
    {
        return $this->hasMany(ShiftType::class);
    }

    /**
     * @return HasManyThrough<Shift>
     */
    public function shifts(): HasManyThrough
    {
        return $this->hasManyThrough(Shift::class, ShiftType::class);
    }

    /**
     * @return BelongsToMany<User>
     */
    public function volunteers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'volunteer_data');
    }

    /**
     * @return Attribute<int, void>
     */
    public function totalNumSpots(): Attribute
    {
        return Attribute::make(
            get: function (mixed $value, array $attributes) {
                return $this->shifts->sum('num_spots');
            }
        );
    }

    /**
     * @return Attribute<float, void>
     */
    public function percentFilled(): Attribute
    {
        return Attribute::make(
            get: function (mixed $value, array $attributes) {
                $total = 100 * ($this->volunteers_count / max(1, $this->total_num_spots));

                return sprintf('%.1f', $total);
            }
        );
    }

    /**
     * @param  Builder<Team>  $query
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('active', 1);
    }

    /**
     * @param  Builder<Team>  $query
     */
    public function scopeEvent(Builder $query, int $eventId): void
    {
        $query->where('event_id', $eventId);
    }
}
