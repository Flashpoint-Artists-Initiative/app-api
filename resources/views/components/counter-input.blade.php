{{-- This could use some cleanup to turn it into a more reusable component --}}
<div
    class="counter-input-field flex flex-row justify-end" 
    x-data="{
        {{-- count comes from the parent, art-project-item --}}
        increment() {
            count++;
            totalCount++;
        },
        decrement() {
            count--;
            totalCount--;
        }
    }"
    x-on:click.stop=""
>
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
