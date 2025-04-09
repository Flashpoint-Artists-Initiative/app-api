@php
    $url = \App\Filament\App\Clusters\UserPages\Pages\TicketTransfers::getUrl();
@endphp
<div class="print-only">
    <p>This ticket is tied to a specific person and will not be valid for anyone else.</p>
    <p>Go to {{ $url }} to transfer this ticket to another person.</p>
</div>
<div><img src="{!! $qrCode !!}"></div>