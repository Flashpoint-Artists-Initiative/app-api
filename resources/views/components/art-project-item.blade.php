<div x-data="{checked: false}">
    {{-- <div class="art-project"> --}}
    <div class="art-project" wire:click.stop="mountAction('openModal', {'id': {{ $project->id }}})">
        <h1>{{ $project->id }}</h1>
        <h2>{{ $project->name }}</h2>
        <p>{{ $project->description }}</p>
        <p>Funding Amount: {{ $project->min_funding }}</p>
        <p>Project Status: {{ $project->project_status }}</p>
        <x-counter-input max=10 name="selectedProjects.{{ $project->id }}" />

        {{-- THESE CANT BE CHECKBOXES :( --}}
        {{-- <x-filament::input.checkbox 
            class="checkbox-lg"
            value="{{ $project->id }}"
            x-on:click.stop=""
            wire:model="selectedProjects"
            @click="checked = event.target.checked; update(event)"
            x-bind:disabled="remaining <= 0 && ! checked"
        /> --}}
        <p>After</p>
    </div>
</div>
@script
<script>
    console.log($wire.selectedProjects);
</script>
@endscript