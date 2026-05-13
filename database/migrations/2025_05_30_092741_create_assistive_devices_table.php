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
        Schema::create('assistive_devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_type_id')->constrained('device_types')->cascadeOnDelete();

            $table->string('serial_no')->unique();
            $table->string('spec')->nullable();      // size, model, power rating
            $table->date('service_date')->nullable();
            $table->foreignId('school_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status', 20)->default('available');            // available | assigned | repair | lost
            $table->timestamps();
            $table->softDeletes();

            $table->index(['device_type_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assistive_devices');
    }
};
