<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\Event;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Attributes\On;
use Livewire\Attributes\Session;
use Livewire\Component;

class EventSelector extends Component
{
    #[Session('active_event_id')]
    public int $eventId = 0;

    protected Collection $events;

    protected function getEvents(): Collection
    {
        if (empty($this->events)) {
            $this->events = Event::where('active', true)->orderBy('start_date')->get()->mapWithKeys(fn (Event $item) => [$item['id'] => $item['name']]);
        }

        return $this->events;
    }

    protected function getCurrentEvent(): string
    {
        return Event::find($this->eventId)->name ?? 'Select an Event';
    }

    public function render(): View
    {
        if ($this->eventId === 0) {
            $this->eventId = (int) $this->getEvents()->keys()->first();
        }

        return view('livewire.event-selector', [
            'events' => $this->getEvents(),
            'currentEvent' => $this->getCurrentEvent(),
        ]);
    }

    #[On('update-active-event')]
    public function updateEventId(int $eventId): void
    {
        $this->dispatch('active-event-updated', $eventId);
        $this->eventId = $eventId;
    }

    public function mount(): void
    {
        $this->eventId = Event::getCurrentEventId();
    }
}
