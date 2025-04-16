<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Mail\OrderCompletedMail;
use App\Models\Ticketing\Order;
use Illuminate\Support\Facades\Mail;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class MailTest extends TestCase
{
    #[Test]
    public function order_completed_mail_content(): void
    {
        /** @var Order $order */
        $order = Order::factory()->create();
        $mail = new OrderCompletedMail($order);

        $mail->assertSeeInOrderInHtml([
            $order->event->name,
            $order->id,
            number_format($order->amount_subtotal / 100, 2),
            number_format($order->amount_tax / 100, 2),
            number_format($order->amount_total / 100, 2),
        ]);

        $mail->assertSeeInOrderInText([
            $order->event->name,
            $order->id,
            number_format($order->amount_subtotal / 100, 2),
            number_format($order->amount_tax / 100, 2),
            number_format($order->amount_total / 100, 2),
        ]);
    }

    #[Test]
    public function order_completed_mail_sending(): void
    {
        Mail::fake();

        Mail::assertNothingSent();

        Order::factory()->create();

        Mail::assertSent(OrderCompletedMail::class);
    }
}
