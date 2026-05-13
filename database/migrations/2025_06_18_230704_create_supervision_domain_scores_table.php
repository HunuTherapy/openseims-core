<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supervision_domain_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supervision_report_id')->constrained('supervision_reports')->cascadeOnDelete();
            $table->string('domain_name');
            $table->integer('score');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supervision_domain_scores');
    }
};
