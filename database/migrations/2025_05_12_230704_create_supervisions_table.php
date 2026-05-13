<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supervision_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('supervisor_id')->constrained('users')->cascadeOnDelete();
            $table->string('supervisor_role', 40);
            $table->date('visit_date');

            $table->text('issues_found')->nullable();
            $table->date('deadline_date')->nullable();
            $table->boolean('resolved')->default(false);
            $table->text('intervention_provided')->nullable();

            $table->foreignId('recipient_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supervision_reports');
    }
};
