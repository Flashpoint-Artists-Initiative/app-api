@php
    $url = \App\Filament\App\Clusters\UserPages\Pages\TicketTransfers::getUrl();
@endphp
<div class="print-only">
    <p>This ticket is tied to a specific user and cannot be transferred physically.</p>
    <p>Go to {{ $url }} to transfer this ticket to another user.</p>
</div>
<div><img src="{!! $qrCode !!}"></div>