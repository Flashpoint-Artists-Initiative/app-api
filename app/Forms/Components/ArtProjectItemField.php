<?php

declare(strict_types=1);

namespace App\Forms\Components;

use Closure;
use Filament\Forms\Components\Field;

class ArtProjectItemField extends Field
{
    protected string $view = 'forms.components.art-project-item';

    protected bool|Closure|null $disableVoting = null;

    public function disableVoting(bool|Closure|null $condition): static
    {
        $this->disableVoting = $condition;

        return $this;
    }

    public function getDisableVoting(): bool
    {
        return $this->evaluate($this->disableVoting);
    }
}
