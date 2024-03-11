<?php

declare(strict_types=1);

namespace App\Models\Volunteering;

use App\Models\User;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $total_num_spots
 * @property float $percent_filled
 */
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

    protected $withCount = [
        'volunteers',
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

    public function totalNumSpots(): Attribute
    {
        return Attribute::make(
            get: function (mixed $value, array $attributes) {
                return $this->shifts->sum('num_spots');
            }
        );
    }

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
