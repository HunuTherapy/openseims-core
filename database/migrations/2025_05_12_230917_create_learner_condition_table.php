<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('learner_condition', function (Blueprint $table) {
            $table->id();
            $table->foreignId('learner_id')->constrained()->cascadeOnDelete();
            $table->foreignId('condition_id')->constrained('conditions')->cascadeOnDelete();
            $table->foreignId('assessment_id')->nullable()->constrained()->nullOnDelete(); // last confirming assessment
            $table->boolean('is_primary')->default(false);
            $table->string('status', 25)->default('provisional');
            $table->string('severity_level', 25)->nullable();
            $table->string('severity_source', 25)->nullable();
            $table->string('disability_onset', 25)->default('congenital');
            // assigned at is now if the disability was assigned at the time of the assessment
            $table->date('assigned_at')->default(now());
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['learner_id', 'condition_id']);
            $table->index(['learner_id', 'is_primary']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('learner_condition');
    }
};
