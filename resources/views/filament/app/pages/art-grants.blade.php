<x-filament-panels::page>
    <div x-data="{
        totalCount: 0,
        max: {{ $maxVotes + 5 }},
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
        <form wire:submit="save">
            @foreach ($projects as $project)
                {{-- <input type="checkbox" wire:model="selectedProjects" value={{ $project->id }} /> --}}
                <x-art-project-item :$project :key="$project->id" :$remainingVotes />
            @endforeach

            <x-filament::button type="submit" x-bind:disabled="remaining > 0" x-bind:class="remaining > 0 && 'opacity-50'">
                Submit Votes
            </x-filament::button>
        </form>
    </div>
<x-filament-actions::modals />
</x-filament-panels::page>
