<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('distribution_points', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name', 150);
            // clinic, government_office, university_office, bakery, water_point, community_kitchen
            $table->string('type', 30);
            $table->string('address', 255)->nullable();
            $table->string('contact_phone', 20)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('distribution_points');
    }
};
