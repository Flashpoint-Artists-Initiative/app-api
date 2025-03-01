<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
>
    <x-filament::section>
        <x-slot name="heading">
            {{ $getRecord()->ticketType->name }}
        </x-slot>
        <x-slot name="headerEnd">
            <p class="text-xl bold">${{ $getRecord()->ticketType->price }}</p>
        </x-slot>
        <x-slot name="description">
            {{ $getRecord()->ticketType->description }}
        </x-slot>

        <div class="flex flex-row justify-between">
            <div>
                @if ($getRecord()->expiration_date)
                    <p class="text-sm">
                        Expires on {{$getRecord()->expiration_date}}
                    </p>
                    
                @endif
            </div>
            <div class="flex flex-row justify-end">
                <label>
                    <span class="px-2">
                        Add to cart
                    </span>
                    <x-filament::input.checkbox wire:model="{{ $getStatePath() }}" />
                </label>
            </div>
        </div>
    </x-filament::section>
</x-dynamic-component>
