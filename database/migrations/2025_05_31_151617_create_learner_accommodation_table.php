<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('learner_accommodation', function (Blueprint $table) {
            $table->id();
            $table->foreignId('learner_id')->constrained()->cascadeOnDelete();
            $table->foreignId('accommodation_type_id')
                ->constrained('accommodation_types')
                ->cascadeOnDelete();

            $table->string('status', 20)->default('requested'); // requested | approved | expired | canceled
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->foreignId('assessment_id')->nullable()->constrained('assessments')->nullOnDelete();
            $table->text('notes')->nullable();

            $table->timestamps();
            $table->index(['accommodation_type_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('learner_accommodation');
    }
};
