<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('participants', function (Blueprint $table) {
            $table->id(); // will be dorsal
            $table->string('dni')->unique()->nullable(false);
            $table->string('first_name');
            $table->string('last_name');
            $table->string('phone')->nullable();
            $table->string('emergency_phone')->nullable();
            $table->enum('gender', ['male','female'])->default('female');
            $table->date('birth_date');
            $table->enum('status', ['not_presented','presented','abandoned','finished'])->default('not_presented');
            $table->string('lunch_sandwich')->nullable();
            $table->string('dinner_sandwich')->nullable();
            $table->enum('shirt_size',['XXS','XS','S','M','L','XL','XXL','XXXL'])->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('participants');
    }
};
