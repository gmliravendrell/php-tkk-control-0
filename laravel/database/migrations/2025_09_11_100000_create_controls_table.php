<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('controls', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('km_point', 5, 2)->nullable(false)->unique();
            $table->string('responsible')->nullable();
            $table->string('phone')->nullable();
            $table->enum('status', ['preparing','open_requested','opened','close_requested','closed'])->default('preparing');
            $table->integer('passed')->default(0);
            $table->integer('missing')->default(0);
            $table->integer('abandoned')->default(0);
            $table->enum('type',['start','control','finish'])->default('control');
            $table->timestamps();
            $table->string('unique_type')->nullable()->storedAs("
                CASE
                    WHEN type IN ('start','finish') THEN type
                    ELSE NULL
                END
            ");
            $table->unique('unique_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('controls');
    }
};
