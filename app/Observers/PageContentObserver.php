<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\PageContent;

class PageContentObserver
{
    public function saving(PageContent $pageContent): bool
    {
        // Only allow one page content per event and page
        if ($pageContent->isDirty('page')) {
            if (PageContent::where('event_id', $pageContent->event_id)
                ->where('page', $pageContent->page)
                ->where('id', '!=', $pageContent->id ?? 0)
                ->exists()
            ) {
                return false;
            }
        }

        return true;
    }
}
