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
        Schema::create('learner_school_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('learner_id')->constrained()->cascadeOnDelete();

            // nullable so you can keep a row for “out-of-school” periods
            $table->foreignId('school_id')->nullable()->constrained()->nullOnDelete();

            // string-backed enum cast in the model (see below)
            $table->string('placement_type', 20)->default('enrolled'); // enrolled | transfer_out | transfer_in | graduated | exited | deceased
            $table->date('start_date');
            $table->date('end_date')->nullable();    // null ⇒ current placement

            // who logged the change (optional)
            $table->foreignId('recorded_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            // ── Indices
            $table->index(['learner_id', 'start_date']);
            $table->index(['school_id', 'start_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('learner_school_history');
    }
};
