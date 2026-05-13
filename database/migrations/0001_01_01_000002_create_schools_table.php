<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('schools', function (Blueprint $table) {
            $table->id();
            $table->string('emis_code')->unique();
            $table->string('name');
            $table->foreignId('district_id')->constrained('districts')->restrictOnDelete();
            $table->string('school_level', 20)->default('primary');
            $table->string('school_type')->default('public');
            $table->boolean('is_inclusive')->default(false);
            $table->boolean('resource_teacher')->default(false);
            $table->unsignedInteger('number_of_teachers')->nullable();
            $table->json('accessibility')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schools');
    }
};
