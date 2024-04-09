<?php

declare(strict_types=1);

namespace App\Models\Volunteering;

use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as ContractsAuditable;

/**
 * @property int $total_num_spots
 * @property float $percent_filled
 */
class ShiftType extends Model implements ContractsAuditable
{
    use Auditable, HasFactory;

    protected $fillable = [
        'team_id',
        'title',
        'description',
        'length',
        'num_spots',
    ];

    /** @var string[] */
    protected $withCount = [
        'volunteers',
    ];

    /** @var string[] */
    protected $with = [
        'requirements',
    ];

    /**
     * @return HasOneThrough<Event>
     */
    public function event(): HasOneThrough
    {
        // Set the keys directly because we're effectively going backwards from the intended way
        return $this->hasOneThrough(
            Event::class,
            Team::class,
            'id', // Foreign Key for team
            'id', // Foreign Key for event
            'team_id', // Local key for shiftType
            'event_id' //Local key for team
        );
    }

    /**
     * @return BelongsTo<Team, ShiftType>
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * @return HasMany<Shift>
     */
    public function shifts(): HasMany
    {
        return $this->hasMany(Shift::class);
    }

    /**
     * @return BelongsToMany<Requirement>
     */
    public function requirements(): BelongsToMany
    {
        return $this->belongsToMany(Requirement::class, 'shift_type_requirements')->withTimestamps();
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
}
