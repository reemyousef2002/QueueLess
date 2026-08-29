<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('crowd_density_reports', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('distribution_point_id')->constrained('distribution_points')->cascadeOnDelete();
            $table->string('density_level', 10); // green, yellow, red
            $table->foreignUuid('reported_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['distribution_point_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crowd_density_reports');
    }
};
