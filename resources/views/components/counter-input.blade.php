@props([
    'count' => 0,
    'name' => 'counter',
])
<div class="counter-input-field" x-data="{
    count: {{ $count }},
    increment() {
        if (this.count < max) { {{-- max is defined in a parent component  --}}
            this.count++;
            totalCount++;
            $wire.$set('{{ $name }}', this.count, false);
        }
    },
    decrement() {
        if (this.count > 0) {
            this.count--;
            totalCount--;
            $wire.$set('{{ $name }}', this.count, false);
        }
    }
}">
    {{-- <input type="text" name="{{ $name }}" x-bind:value="count" wire:model="{{ $name }}" /> --}}
    <div class="flex flex-row justify-end" x-on:click.stop="">
        <x-filament::button x-on:click="decrement()" x-bind:disabled="count <= 0"  x-bind:class="count <= 0 && 'opacity-50'">
            -
        </x-filament::button>
        <x-filament::badge class="no-radius">
            <span class="text-lg" x-text="count"></span>
        </x-filament::badge>
        <x-filament::button x-on:click="increment()" x-bind:disabled="remaining <= 0" x-bind:class="remaining <= 0 && 'opacity-50'">
            +
        </x-filament::button>
    </div>
</div>
