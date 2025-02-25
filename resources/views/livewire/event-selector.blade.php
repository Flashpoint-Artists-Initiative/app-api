@if (filled($events))
    <div>
        <x-filament::dropdown
            placement="bottom-start"
            @updateEventId="$refresh"
            key="active-event-selector"
        >
            <x-slot name="trigger">
                <x-filament::button icon="heroicon-o-calendar" color="gray" outlined="false">
                    {{ $currentEvent }}
                </x-filament::button>
            </x-slot>

            <x-filament::dropdown.list>
                @foreach ($events as $id => $name)
                    <x-filament::dropdown.list.item
                        wire:click="updateEventId({{ $id }})"
                        wire:key="event-{{ $id }}"
                        {{-- color="{{ $credentials === $current ? 'primary' : 'gray' }}" --}}
                    >
                        {{ "$name" }}
                    </x-filament::dropdown.list.item>
                @endforeach
            </x-filament::dropdown.list>
        </x-filament::dropdown>
    </div>
@endif
