<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLearnerAssessmentHistoryTable extends Migration
{
    public function up(): void
    {
        Schema::create('learner_assessment_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('learner_id')->constrained()->cascadeOnDelete();
            $table->morphs('centerable');
            $table->enum('event_type', ['screening', 'assessment']);
            $table->foreignId('referred_to_center_id')->nullable()->constrained('assessment_centers')->nullOnDelete();
            $table->timestamp('event_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['learner_id', 'event_type']);
        });
    }

    public function down(): void
    {
        Schema::table('learner_assessment_history', function (Blueprint $table) {
            $table->dropConstrainedForeignId('referred_to_center_id');
            $table->dropMorphs('centerable');
        });

        Schema::dropIfExists('learner_assessment_history');
    }
}
