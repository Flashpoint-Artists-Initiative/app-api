<?php

declare(strict_types=1);

namespace App\Models\Volunteering;

use App\Models\Event;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Support\Facades\DB;

/**
 * @property int $end_offset
 * @property string $title
 * @property string $start_datetime
 * @property int $volunteers_count
 * @property float $percent_filled
 */
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

    protected $withCount = [
        'volunteers',
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

    public function team(): HasOneThrough
    {
        return $this->hasOneThrough(Team::class, ShiftType::class);
    }

    public function event(): Event
    {
        $eventData = DB::select('SELECT events.* FROM shifts 
            LEFT JOIN shift_types ON shift_types.id = shifts.shift_type_id 
            LEFT JOIN teams ON teams.id = shift_types.team_id 
            LEFT JOIN events ON events.id = teams.event_id 
            WHERE shifts.id = ?',
            [$this->id]);

        return Event::hydrate($eventData)->first();
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

    public function endOffset(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->start_offset + $this->length
        );
    }

    public function title(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->shiftType->title
        );
    }

    public function startDatetime(): Attribute
    {
        return Attribute::make(
            get: function () {
                $eventStart = $this->event()->start_date;
                $start = new Carbon($eventStart);
                $start->addMinutes($this->start_offset);

                return $start->format('D, M j, g:i a');
            }
        );
    }

    public function endDatetime(): Attribute
    {
        return Attribute::make(
            get: function () {
                $eventStart = $this->event()->start_date;
                $start = new Carbon($eventStart);
                $start->addMinutes($this->end_offset);

                return $start->format('D, M j, g:i a');
            }
        );
    }

    public function percentFilled(): Attribute
    {
        return Attribute::make(
            get: function (mixed $value, array $attributes) {
                $total = 100 * ($this->volunteers_count / max(1, $this->num_spots));

                return sprintf('%.1f', $total);
            }
        );
    }

    public function overlapsWith(Shift $shift): bool
    {
        return max($this->start_offset, $shift->start_offset) < min($this->end_offset, $shift->end_offset);
    }
}