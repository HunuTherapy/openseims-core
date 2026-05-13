<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teachers', function (Blueprint $table) {
            $table->id();
            $table->string('teacher_no')->unique();
            $table->string('teacher_type');
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->unique()->constrained()->nullOnDelete();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('class')->nullable();
            $table->string('qualification')->nullable();
            $table->text('special_education_background')->nullable();
            $table->boolean('training_on_inclusion')->default(false);
            $table->text('skills')->nullable();
            $table->text('other_qualifications')->nullable();
            $table->unsignedInteger('in_service_trainings_attended')->nullable();
            $table->boolean('sen_certified')->default(false);
            $table->boolean('is_deployed')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teachers');
    }
};
