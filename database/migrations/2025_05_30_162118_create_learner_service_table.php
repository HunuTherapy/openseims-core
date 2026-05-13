<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('learner_service', function (Blueprint $table) {
            $table->id();

            // Foreign keys
            $table->foreignId('learner_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_type_id')->constrained('service_types')->cascadeOnDelete();

            // Frequency configuration
            $table->unsignedTinyInteger('frequency_value')->nullable(); // e.g. 3
            $table->string('frequency_unit', 10)->nullable(); // e.g. 'week'
            $table->string('frequency_type', 20)->default('per_period'); // e.g. 'fixed'

            // Status & metadata
            $table->string('status', 20)->default('needed'); // needed | scheduled | in_progress | completed
            $table->date('requested_at')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->text('notes')->nullable();

            // Timestamps & indexes
            $table->timestamps();
            $table->index(['service_type_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('learner_service');
    }
};
