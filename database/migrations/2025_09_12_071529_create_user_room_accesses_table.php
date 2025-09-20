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
        Schema::create('user_room_accesses', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->string('user_uuid');
            $table->string('room_uuid');
            $table->date('date');
            $table->string('start_time');
            $table->string('end_time');
            $table->string('status');
            $table->string('service_uuid')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_room_accesses');
    }
};
