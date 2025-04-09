<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PageContentEnum;
use App\Observers\PageContentObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[ObservedBy(PageContentObserver::class)]
class PageContent extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'page',
        'content',
    ];

    protected function casts()
    {
        return [
            'page' => PageContentEnum::class,
        ];
    }

    /**
     * @return BelongsTo<Event, $this>
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }
}
