<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Mail\OrderCompletedMail;
use App\Models\Ticketing\Order;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class MailTest extends TestCase
{
    use LazilyRefreshDatabase;

    public bool $seed = true;

    public function test_order_completed_mail_content(): void
    {
        $order = Order::first();
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

    public function test_order_completed_mail_sending(): void
    {
        Mail::fake();

        Mail::assertNothingSent();

        Order::factory()->create();

        Mail::assertSent(OrderCompletedMail::class);
    }
}
