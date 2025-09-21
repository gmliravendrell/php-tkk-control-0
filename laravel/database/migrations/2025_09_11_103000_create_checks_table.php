<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('checks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('control_id')->constrained('controls')->onDelete('cascade');
            $table->foreignId('participant_id')->constrained('participants')->onDelete('cascade');
            $table->timestamp('checked_at')->useCurrent();
            $table->enum('type', ['check','abandon']);
            $table->timestamps();
             $table->unique(['control_id', 'participant_id','type'], 'unique_control_participant');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('checks');
    }
};
