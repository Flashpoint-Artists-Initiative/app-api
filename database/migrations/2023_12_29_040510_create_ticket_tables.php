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
        // TicketType
        Schema::create('ticket_types', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->unsignedInteger('event_id');
            $table->string('name');
            $table->dateTime('sale_start_date')->nullable();
            $table->dateTime('sale_end_date')->nullable();
            $table->unsignedInteger('quantity')->default(0);
            $table->unsignedInteger('price');
            $table->text('description');
            $table->boolean('active')->default(false);
            $table->softDeletes();
        });

        // PurchasedTicket
        Schema::create('purchased_tickets', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->unsignedInteger('ticket_type_id');
            $table->unsignedInteger('order_id')->nullable();
            $table->unsignedInteger('user_id');
        });

        // ReservedTicket
        Schema::create('reserved_tickets', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->unsignedInteger('ticket_type_id');
            $table->string('email')->nullable();
            $table->unsignedInteger('user_id')->nullable();
            $table->dateTime('expiration_date')->nullable();
            $table->unsignedInteger('purchased_ticket_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ticket_types');
        Schema::dropIfExists('purchased_tickets');
        Schema::dropIfExists('reserved_tickets');
    }
};
