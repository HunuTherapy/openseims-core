<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('officers', static function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('role')->default('Officer');
            $table->boolean('formal_training')->default(false);
            $table->string('phone')->unique();
            $table->boolean('is_deployed')->default(false);
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('officers');
    }
};
