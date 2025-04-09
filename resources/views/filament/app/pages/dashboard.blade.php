@php
    use \App\Filament\Admin\Resources\EventResource\Pages\EditAppDashboardContent;
@endphp
<x-filament-panels::page>
    @if ($event?->dashboardContent)
    <div class="rich-text-content">
        {!! str($event?->dashboardContent?->formattedContent)->sanitizeHtml() !!}
    </div>
    @elseif (Auth::user()->can('events.edit') && $event)
    <span>Add content to the dashboard in the <x-filament::link href="{{ EditAppDashboardContent::getUrl(['record' => $event->id], panel: 'admin') }}">Admin Panel</x-filament::link></span>
    @endif
</x-filament-panels::page>