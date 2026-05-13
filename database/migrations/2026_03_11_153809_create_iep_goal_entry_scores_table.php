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
        Schema::create('iep_goal_entry_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('iep_goal_entry_id')->constrained()->cascadeOnDelete();
            $table->date('recorded_at');
            $table->integer('score');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('iep_goal_entry_scores');
    }
};
