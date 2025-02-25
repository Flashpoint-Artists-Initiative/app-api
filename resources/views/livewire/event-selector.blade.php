{{-- The blank lines around the dropdown block are important for some reason --}}
@if (filled($events))

        <x-filament::dropdown
            placement="bottom-start"
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
                        x-on:click="toggle"
                    >
                        {{ "$name" }}
                    </x-filament::dropdown.list.item>
                @endforeach
            </x-filament::dropdown.list>
        </x-filament::dropdown>

@endif
