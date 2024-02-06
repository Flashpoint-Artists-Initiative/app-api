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
        Schema::create('waivers', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->unsignedInteger('event_id');
            $table->string('title');
            $table->text('content');
            $table->boolean('minor_waiver')->default(false);
        });

        Schema::create('completed_waivers', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->unsignedInteger('waiver_id');
            $table->unsignedInteger('user_id');
            $table->json('form_data')->nullable();
            $table->boolean('paper_completion')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('waivers');
        Schema::dropIfExists('completed_waivers');
    }
};
