<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('teams', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->softDeletes();
            $table->unsignedInteger('event_id');
            $table->string('name');
            $table->text('description');
            $table->string('email')->nullable();
            $table->boolean('active')->default(true);
        });

        Schema::create('shift_types', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->softDeletes();
            $table->unsignedInteger('team_id');
            $table->string('title');
            $table->text('description');
            $table->unsignedInteger('length'); // minutes
            $table->unsignedInteger('num_spots');
        });

        Schema::create('shifts', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->unsignedInteger('shift_type_id');
            $table->unsignedInteger('start_offset'); // minutes
            $table->boolean('multiplier')->nullable();
            $table->unsignedInteger('length')->nullable();
            $table->unsignedInteger('num_spots')->nullable();
        });

        Schema::create('requirements', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->softDeletes();
            $table->string('name');
            $table->string('icon')->nullable();
            $table->text('description')->nullable();
        });

        Schema::create('shift_signups', function (Blueprint $table) {
            $table->timestamps();
            $table->unsignedInteger('shift_id');
            $table->unsignedInteger('user_id');
        });

        Schema::create('shift_type_requirements', function (Blueprint $table) {
            $table->timestamps();
            $table->unsignedInteger('shift_type_id');
            $table->unsignedInteger('requirement_id');
        });

        DB::statement($this->dropView());
        DB::statement($this->createView());
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teams');
        Schema::dropIfExists('shifts');
        Schema::dropIfExists('shift_types');
        Schema::dropIfExists('requirements');
        Schema::dropIfExists('shift_signups');
        Schema::dropIfExists('shift_type_requirements');

        DB::statement($this->dropView());
    }

    protected function createView(): string
    {
        return <<<'SQL'
        create view `volunteer_data` as
        select shift_signups.*, shifts.shift_type_id, shift_types.team_id 
            from shift_signups 
            left join shifts on shift_signups.shift_id = shifts.id 
            left join shift_types on shifts.shift_type_id = shift_types.id;
        SQL;
    }

    protected function dropView(): string
    {
        return <<<'SQL'
        drop view if exists `volunteer_data`;
        SQL;
    }
};
