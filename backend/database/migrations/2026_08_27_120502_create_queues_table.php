<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('queues', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('distribution_point_id')->constrained('distribution_points')->cascadeOnDelete();
            $table->string('status', 20)->default('open'); // open, paused, closed
            $table->unsignedInteger('current_number')->default(0);
            $table->decimal('avg_service_minutes', 5, 2)->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('queues');
    }
};
