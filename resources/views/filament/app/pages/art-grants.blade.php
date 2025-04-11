<x-filament-panels::page>
    <div x-data="{
        count: 0,
        max: {{ $maxVotes }},
        remaining: 999,
        init() {
            this.remaining = this.max;
        },
        update(e) {
            if (e.target.checked) {
                this.count++;
            } else {
                this.count--;
            }

            this.remaining = this.max - this.count;
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

            <x-filament::button type="submit">
                Save
            </x-filament::button>
        </form>
    </div>
<x-filament-actions::modals />
</x-filament-panels::page>
