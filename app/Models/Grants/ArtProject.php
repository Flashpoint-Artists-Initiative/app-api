<?php

declare(strict_types=1);

namespace App\Models\Grants;

use App\Enums\ArtProjectStatus;
use App\Enums\GrantFundingStatus;
use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;

/**
 * @property-read Event $event
 * @property-read User $user
 * @property-read GrantFundingStatus $fundingStatus
 * @property-read float $fundedTotal
 */
class ArtProject extends Model
{
    use Auditable, HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'user_id',
        'event_id',
        'artist_name',
        'description',
        'budget_link',
        'min_funding',
        'max_funding',
        'project_status',
    ];

    protected $withCount = [
        'votes',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProjectImage::class);
    }

    public function votes(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'project_user_votes')->withTimestamps();
    }

    public function fundedTotal(): Attribute
    {
        return Attribute::make(
            get: function () {
                return $this->votes()->count() * $this->event->dollarsPerVote;
            },
        );
    }

    public function fundingStatus(): Attribute
    {
        return Attribute::make(
            get: function () {
                $funding = $this->fundedTotal;

                return match (true) {
                    $funding >= $this->max_funding => GrantFundingStatus::MaxReached,
                    $funding >= $this->min_funding => GrantFundingStatus::MinReached,
                    default => GrantFundingStatus::Unfunded,
                };
            },
        );
    }

    public function vote(User $user): void
    {
        if (! $this->event->votingEnabled) {
            throw new \Exception('Grant voting is closed for this event');
        }

        if ($this->project_status !== ArtProjectStatus::Approved->value) {
            throw new \Exception('Only approved projects can be voted on');
        }

        if ($this->votes()->where('user_id', $user->id)->exists()) {
            throw new \Exception('User has already voted for this project');
        }

        if ($this->fundingStatus === GrantFundingStatus::MaxReached) {
            throw new \Exception('Project has already reached maximum funding');
        }

        $this->votes()->attach($user->id);
    }
}
