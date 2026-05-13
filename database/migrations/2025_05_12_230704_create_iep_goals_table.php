<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Query\Expression;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('iep_goals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('learner_id')->constrained()->cascadeOnDelete();
            // Engagement frequency, split into count + unit
            $table->integer('frequency_value')->nullable();
            $table->string('frequency_unit', 10)->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('parental_consent');
            $table->string('program_placement');
            $table->string('program_placement_other')->nullable();
            $table->json('related_services')->default(new Expression('(JSON_ARRAY())'));
            $table->string('related_services_other')->nullable();
            $table->string('evaluation_decision')->nullable();
            $table->string('goal_type');
            $table->text('recommendation_details')->nullable();
            $table->enum('status', ['on_track', 'lagging', 'achieved']);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('iep_goals');
    }
};
