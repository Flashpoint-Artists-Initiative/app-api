<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('user_email');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('event_id');
            $table->unsignedInteger('cart_id');
            $table->unsignedInteger('amount_subtotal');
            $table->unsignedInteger('amount_total');
            $table->unsignedInteger('amount_tax');
            $table->unsignedInteger('amount_fees');
            $table->unsignedInteger('quantity');
            $table->string('stripe_checkout_id')->index()->collation('utf8mb4_bin');
            $table->boolean('refunded')->default(false);
            $table->json('ticket_data');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
