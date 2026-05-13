<?php

// use Illuminate\Database\Migrations\Migration;
// use Illuminate\Database\Schema\Blueprint;
// use Illuminate\Support\Facades\Schema;

// return new class extends Migration {
//     public function up(): void
//     {
//         Schema::create('learners', function (Blueprint $table) {
//             $table->id();
//             $table->string('first_name');
//             $table->string('last_name');
//             $table->foreignId('school_id')->constrained()->cascadeOnDelete();
//             $table->enum('sex', ['M', 'F']);
//             $table->date('date_of_birth');
//             $table->date('enrol_date');
//             $table->enum('status', ['enrolled', 'transferred', 'exited']);
//             $table->timestamps();
//         });
//     }

//     public function down(): void
//     {
//         Schema::dropIfExists('learners');
//     }
// };

// <?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('learners', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->nullable()->constrained()->nullOnDelete()->comment('The school the learner currently attends');
            $table->string('first_name');
            $table->string('middle_name')->nullable(); // Optional middle name
            $table->string('last_name');
            $table->string('sex', 1); // 'M' or 'F'
            $table->date('date_of_birth')->comment('Date of birth in YYYY-MM-DD format');
            $table->string('primary_contact_name', 100)->nullable();
            $table->string('primary_contact_phone', 100)->nullable();
            $table->string('secondary_contact_phone', 100)->nullable();
            $table->string('primary_contact_email')->nullable()->unique();
            $table->date('enrol_date')->nullable();
            $table->string('class', 8)->nullable()->comment('Current grade / class level'); // 1-9 for KG/P1-JHS3 etc.
            $table->string('primary_language', 50)->nullable();
            $table->string('status', 20)->default('enrolled');
            $table->date('referred_at')->nullable();
            $table->boolean('specialist_visit_completed')->nullable();
            $table->text('academic_strengths')->nullable();
            $table->text('academic_weaknesses')->nullable();
            $table->text('social_life_observations')->nullable();
            $table->text('extracurricular_activity_notes')->nullable();
            $table->text('specific_needs')->nullable();
            $table->timestamps();
            $table->softDeletes();
            // Helpful composite index for school dashboards
            $table->index(['school_id', 'class'], 'idx_school_class');
            $table->index('class', 'idx_class');

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('learners');
    }
};
