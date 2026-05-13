<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCpdModuleIePracticeTable extends Migration
{
    public function up(): void
    {
        Schema::create('cpd_module_ie_practice', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cpd_module_id')->constrained('cpd_modules')->cascadeOnDelete();
            $table->foreignId('ie_practice_id')->constrained('ie_practices')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['cpd_module_id', 'ie_practice_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cpd_module_ie_practice');
    }
}
