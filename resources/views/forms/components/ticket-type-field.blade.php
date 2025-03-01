<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
>
    <div x-data="{
        state: $wire.$entangle('{{ $getStatePath() }}'),
        increment() {
            if (this.state < Math.min(4,{{ $getRecord()->remainingTicketCount }})) {
                this.state++;
            }
        },
        decrement() {
            if (this.state > 0) {
                this.state--;
            }
        }
    }">
        <x-filament::section>
            <x-slot name="heading">
                {{ $getRecord()->name }}
            </x-slot>
            <x-slot name="headerEnd">
                <p class="text-xl bold">${{ $getRecord()->price }}</p>
            </x-slot>
            <x-slot name="description">
                {{ $getRecord()->description }}
            </x-slot>

            <div class="flex flex-row justify-end">
                <x-filament::button x-on:click="decrement()">
                    -
                </x-filament::button>
                <x-filament::badge>
                    <span class="text-lg" x-text="state"></span>
                </x-filament::badge>
                <x-filament::button x-on:click="increment()">
                    +
                </x-filament::button>
            </div>
        </x-filament::section>
    </div>
</x-dynamic-component>
