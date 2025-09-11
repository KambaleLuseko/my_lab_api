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
        Schema::create('room_managers', function (Blueprint $table) {
            $table->id();
             $table->string('uuid')->unique();
            $table->string('room_uuid');
            $table->string('user_uuid');
            $table->date('date');
            $table->string('start_time')->nullable();
            $table->string('end_time')->nullable();
            $table->string('status')->default('Active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('room_managers');
    }
};
