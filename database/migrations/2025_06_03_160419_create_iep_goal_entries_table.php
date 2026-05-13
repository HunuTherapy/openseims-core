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
        Schema::create('iep_goal_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('iep_goal_id')->constrained()->cascadeOnDelete();
            $table->integer('baseline');
            $table->text('baseline_description')->nullable();
            $table->string('instruction_area');
            $table->integer('target');
            $table->text('target_description')->nullable();
            $table->date('last_review_at')->nullable();
            $table->string('completion_status')->default('not_started');
            $table->boolean('recommend_goal_change')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('iep_goal_entries');
    }
};
