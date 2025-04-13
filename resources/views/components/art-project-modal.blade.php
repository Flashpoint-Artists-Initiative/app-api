<div>
    <x-filament::badge class="inline-grid" :color="$project->funding_status->getColor()">
        {{ $project->funding_status->getLabel() }}
    </x-filament::badge>
    <p><span class="font-bold">Artist:</span> {{ $project->artist_name }}</p>
    <p>{{ $project->description }}</p>
    <div class="flex flex-col gap-2">
        <p><span class="font-bold">Minimum Funding Requested:</span> ${{ $project->min_funding }}</p>
        <p><span class="font-bold">Maximum Funding Requested:</span> ${{ $project->max_funding }}</p>
    </div>
</div>