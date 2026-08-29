<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('community_updates', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('distribution_point_id')->constrained('distribution_points')->cascadeOnDelete();
            $table->foreignUuid('reporter_id')->constrained('users')->cascadeOnDelete();
            // resource_arrived, resource_depleted, queue_paused, queue_resumed, other
            $table->string('update_type', 30);
            $table->string('message', 255)->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('community_updates');
    }
};
