<?php

declare(strict_types=1);

namespace App\Models\Grants;

use App\Enums\ArtProjectStatusEnum;
use App\Enums\GrantFundingStatusEnum;
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
use OwenIt\Auditing\Contracts\Auditable as ContractsAuditable;

/**
 * @property-read Event $event
 * @property-read User $user
 * @property-read GrantFundingStatusEnum $fundingStatus
 * @property-read float $fundedTotal
 */
class ArtProject extends Model implements ContractsAuditable
{
    use Auditable, HasFactory, SoftDeletes, Auditable;

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

    public function artistName(): Attribute
    {
        return Attribute::make(
            get: function (?string $value) {
                return $value ?: $this->user->display_name;
            },
        );
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
                    $funding >= $this->max_funding => GrantFundingStatusEnum::MaxReached,
                    $funding >= $this->min_funding => GrantFundingStatusEnum::MinReached,
                    default => GrantFundingStatusEnum::Unfunded,
                };
            },
        );
    }

    public function vote(User $user): void
    {
        if (! $this->event->voting_enabled) {
            throw new \Exception('Grant voting is closed for this event');
        }

        if ($this->project_status !== ArtProjectStatusEnum::Approved->value) {
            throw new \Exception('Only approved projects can be voted on');
        }

        if ($this->votes()->where('user_id', $user->id)->exists()) {
            throw new \Exception('User has already voted for this project');
        }

        if ($this->fundingStatus === GrantFundingStatusEnum::MaxReached) {
            throw new \Exception('Project has already reached maximum funding');
        }

        $this->votes()->attach($user->id);
    }
}
