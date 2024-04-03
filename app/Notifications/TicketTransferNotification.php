<?php

declare(strict_types=1);

namespace App\Notifications;

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

        $message = (new MailMessage())
            ->subject('Pending Ticket Transfer for ' . $eventName)
            ->line('You have a pending ticket transfer for ' . $eventName)
            ->action('Click Here to Accept your ' . $ticketString, url('/'))
            ->line('If you already have an account, click the link and login to accept your ' . strtolower($ticketString) .
                ". Otherwise, you'll be prompted to create an account before continuing.")
            ->salutation(' ');

        return $message;
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
