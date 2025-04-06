<x-filament-panels::page>
    @if ($this->hasWaiver)
        {{ $this->form }}
    @else
        There is currently no waiver for this event.
    @endif

    @if (count($relationManagers = $this->getRelationManagers()))
        <x-filament-panels::resources.relation-managers
            :active-manager="$this->activeRelationManager"
            :managers="$relationManagers"
            :owner-record="$record"
            :page-class="static::class"
        />
    @endif
</x-filament-panels::page>