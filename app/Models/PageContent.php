<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PageContentEnum;
use App\Observers\PageContentObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Casts\Attribute;
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

    public function formattedContent(): Attribute
    {
        return Attribute::make(
            get: function () {
                $document = \DOM\HTMLDocument::createFromString($this->content, LIBXML_NOERROR | LIBXML_HTML_NOIMPLIED);
                foreach ($document->getElementsByTagName('a') as $link) {
                    // $link->setAttribute('class', 'font-semibold text-sm text-custom-600 dark:text-custom-400 group-hover/link:underline group-focus-visible/link:underline');
                    // $link->setAttribute('style', '--c-400:var(--primary-400);--c-600:var(--primary-600);');
                    $link->innerHTML = '<span class="font-semibold text-sm text-custom-600 dark:text-custom-400 group-hover/link:underline group-focus-visible/link:underline" style="--c-400:var(--primary-400);--c-600:var(--primary-600);">' . $link->innerHTML . '</span>';
                }

                // dd($document->saveHtml());
                return $document->saveHtml();
            }
        );
    }
}
