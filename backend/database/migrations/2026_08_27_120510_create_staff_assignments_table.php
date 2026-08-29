<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Junction table between users (staff/volunteer) and the distribution
     * points they are allowed to manage. Not in the ERD from the analysis
     * doc, but required to implement NFR-05 ("Location staff and volunteers
     * shall only be able to manage or update the distribution points
     * assigned to them").
     */
    public function up(): void
    {
        Schema::create('staff_assignments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('distribution_point_id')->constrained('distribution_points')->cascadeOnDelete();
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['user_id', 'distribution_point_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_assignments');
    }
};
