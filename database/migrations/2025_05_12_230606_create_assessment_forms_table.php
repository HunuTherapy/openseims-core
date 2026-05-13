<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assessment_forms', static function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->text('description')->nullable();
            $table->string('url')->nullable();
            $table->string('version')->nullable();
            $table->json('form_json')->nullable(); // meant to hold dynamic form definitions (e.g., questions, scoring logic, etc.).
            $table->boolean('active')->default(true);
            $table->unsignedBigInteger('responses_count')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assessment_forms');
    }
};
