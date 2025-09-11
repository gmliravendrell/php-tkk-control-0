<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('participants', function (Blueprint $table) {
            $table->id(); // will be dorsal
            $table->string('dni')->nullable();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('phone')->nullable();
            $table->string('emergency_phone')->nullable();
            $table->enum('status', ['not_presented','presented','abandoned','finished'])->default('not_presented');
            $table->boolean('lunch_sandwich')->default(false);
            $table->boolean('dinner_sandwich')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('participants');
    }
};
