<?php

declare(strict_types=1);

namespace App\Filament\App\Pages;

use App\Models\Event;
use Livewire\Attributes\On;

class Dashboard extends \Filament\Pages\Dashboard
{
    protected static string $view = 'filament.app.pages.dashboard';

    public ?Event $event = null;

    #[On('active-event-updated')]
    public function mount(): void
    {
        $this->event = Event::getCurrentEvent();
    }
}
