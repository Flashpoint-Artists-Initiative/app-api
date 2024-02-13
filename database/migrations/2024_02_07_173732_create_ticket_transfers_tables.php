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
        Schema::create('ticket_transfers', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->unsignedInteger('user_id');
            $table->string('recipient_email');
            $table->unsignedInteger('recipient_user_id')->nullable();
            $table->boolean('completed')->default(false);
        });

        Schema::create('ticket_transfer_items', function (Blueprint $table) {
            $table->unsignedInteger('ticket_transfer_id');
            $table->morphs('ticket');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ticket_transfers');
        Schema::dropIfExists('ticket_transfer_items');
    }
};
