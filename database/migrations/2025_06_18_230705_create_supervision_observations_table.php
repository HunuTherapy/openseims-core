<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supervision_observations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supervision_report_id')->constrained('supervision_reports')->cascadeOnDelete();
            $table->text('issues_found')->nullable();
            $table->text('intervention_provided')->nullable();
            $table->date('deadline_date')->nullable();
            $table->boolean('resolved')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supervision_observations');
    }
};
