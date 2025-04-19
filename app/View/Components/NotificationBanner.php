<?php

declare(strict_types=1);

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class NotificationBanner extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct(public string $color = 'primary')
    {
        //
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): string
    {
        return <<<'blade'
            <section 
                style="--c-100:var(--{{ $color }}-100);--c-400:var(--{{ $color }}-400);--c-500:var(--{{ $color }}-500);--c-600:var(--{{ $color }}-600);" 
                {{ $attributes->merge(['class' => 'mb-2 ring-1 rounded-xl ring-custom-600 bg-custom-100 dark:ring-custom-500 dark:bg-custom-400/10']) }}
            >
            <div class="p-2">
                {{ $slot }}
            </div>
        </section>
        blade;
    }
}
