<x-filament-panels::page>
    <div x-data="{
        totalCount: 0,
        max: {{ $maxVotes }},
        remaining: {{ $maxVotes }},
        init() {
            this.remaining = this.max;
            $watch('totalCount', (value) => {
                this.remaining = this.max - value;
            });
        }
    }"
    x-init="init()"
    >
        <p> VOTES REMAINING: <span x-text="remaining"></span></p>
        <x-filament-panels::form wire:submit="submitVotes">
            {{ $this->form }}
            <x-filament::button type="submit">
                Submit Votes
            </x-filament::button>
        </x-filament-panels::form>
    </div>
<x-filament-actions::modals />
</x-filament-panels::page>
