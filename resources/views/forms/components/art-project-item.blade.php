<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
>
    <div
        class="art-project" 
        wire:click.stop="mountAction('openModal', {'id': {{ $getRecord()->id }}})"
        x-data="{ count: $wire.$entangle('{{ $getStatePath() }}') }"
    >
        <h1>{{ $getRecord()->id }}</h1>
        <h2>{{ $getRecord()->name }}</h2>
        <p>{{ $getRecord()->description }}</p>
        <p>Funding Amount: {{ $getRecord()->min_funding }}</p>
        <p>Project Status: {{ $getRecord()->project_status }}</p>
        <x-counter-input />
    </div>
</x-dynamic-component>