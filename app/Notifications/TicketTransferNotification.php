<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Filament\App\Clusters\UserPages\Pages\TicketTransfers;
use App\Models\Ticketing\TicketTransfer;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class TicketTransferNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        protected TicketTransfer $ticketTransfer
    ) {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $eventName = $this->ticketTransfer->event->name;
        $ticketString = Str::plural('Ticket', $this->ticketTransfer->ticketCount);
        $url = TicketTransfers::getUrl();

        $message = (new MailMessage)
            ->subject($eventName . ': Pending Ticket Transfer')
            ->line('You have a pending ticket transfer for ' . $eventName)
            ->action('Click Here to Accept your ' . $ticketString, $url)
            ->line('If you already have an account, click the link and login to accept your ' . strtolower($ticketString))
            ->line("Otherwise, you'll be prompted to create an account before continuing.  Be sure to use this email address to create your account.")
            ->salutation(' ');

        return $message;
    }
}
