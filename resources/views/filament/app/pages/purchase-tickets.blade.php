<x-filament-panels::page>
    <x-filament-panels::form wire:submit="complete" onkeydown="return event.key != 'Enter';">
        {{ $this->form }}
    </x-filament-panels::form>
</x-filament-panels::page>
