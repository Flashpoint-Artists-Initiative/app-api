<?php

declare(strict_types=1);

namespace App\Models\Volunteering;

use App\Models\User;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Shift extends Model
{
    use HasFactory;

    protected $fillable = [
        'shift_type_id',
        'start_offset',
        'length',
        'num_spots',
    ];

    protected $with = [
        'shiftType',
    ];

    public function shiftType(): BelongsTo
    {
        return $this->belongsTo(ShiftType::class);
    }

    public function volunteers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'shift_signups')->as('signup')->withTimestamps();
    }

    public function requirements(): BelongsToMany
    {
        return $this->belongsToMany(Requirement::class, 'shift_requirements')->withTimestamps();
    }

    // Define accessors to pull default values from the shiftType if none are set
    public function length(): Attribute
    {
        return Attribute::make(
            get: function (?int $length) {
                return $length ?? $this->shiftType->length;
            }
        );
    }

    public function numSpots(): Attribute
    {
        return Attribute::make(
            get: function (?int $numSpots) {
                return $numSpots ?? $this->shiftType->num_spots;
            }
        );
    }
}
