<?php

declare(strict_types=1);

namespace App\Mail;

use App\Filament\App\Clusters\UserPages\Pages\Tickets;
use App\Models\Ticketing\ReservedTicket;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\HtmlString;

class SingleReservedTicketCreatedMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(protected ReservedTicket $reservedTicket)
    {
        //
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $eventName = $this->reservedTicket->ticketType->event->name;

        return new Envelope(
            from: new Address(config('mail.from.address'), config('mail.from.name')),
            replyTo: [new Address(config('mail.reply_to.address'), config('mail.reply_to.name'))],
            subject: $eventName . ': You have been given a reserved ticket',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        if ($this->reservedTicket->ticketType->price === 0) {
            return $this->freeContent();
        }

        return $this->reservedContent();
    }

    protected function freeContent(): Content
    {
        $eventName = $this->reservedTicket->ticketType->event->name;
        $url = Tickets::getUrl(panel: 'app');

        $message = (new MailMessage)
            ->subject($eventName . ': You have been given a ticket')
            ->line('You have been granted a ticket for ' . $eventName . '.')
            ->action('View your ticket', $url)
            ->line('If you already have an account, click the link and login to view your ticket.')
            ->line("Otherwise, you'll be prompted to create an account before continuing.  Be sure to use this email address to create your account.")
            ->salutation(' ');

        return new Content(
            htmlString: (string) $message->render(),
        );
    }

    protected function reservedContent(): Content
    {
        $eventName = $this->reservedTicket->ticketType->event->name;
        $expirationDate = $this->reservedTicket->final_expiration_date->format('l, F jS, Y \a\t g:ia');
        $price = '$' . $this->reservedTicket->ticketType->price;
        $name = $this->reservedTicket->ticketType->name;
        $url = Tickets::getUrl(panel: 'app');

        $message = (new MailMessage)
            ->line('You have been granted a reserved ticket for ' . $eventName . '.')
            ->line(new HtmlString("<b>{$name} - {$price}</b>"))
            ->action('View your reserved ticket', $url)
            ->line("Your reserved ticket will expire on {$expirationDate}.")
            ->line('If you already have an account, click the link and login to view your reserved ticket.')
            ->line("Otherwise, you'll be prompted to create an account before continuing.  Be sure to use this email address to create your account.")
            ->salutation(' ');

        return new Content(
            htmlString: (string) $message->render(),
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
