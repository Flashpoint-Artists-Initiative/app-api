<?php

declare(strict_types=1);

namespace App\View\Components;

use App\Models\Grants\ArtProject;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class ArtProjectItem extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct(public ArtProject $project, public int $remainingVotes) {}

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.art-project-item');
    }
}
