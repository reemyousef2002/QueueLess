<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('resource_statuses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('distribution_point_id')->constrained('distribution_points')->cascadeOnDelete();
            $table->string('resource_type', 50); // water, bread, flour, medical_supplies, ...
            $table->string('availability', 20)->default('unknown'); // available, limited, depleted, unknown
            $table->foreignUuid('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->unique(['distribution_point_id', 'resource_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resource_statuses');
    }
};
