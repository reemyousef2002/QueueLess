<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A per-point photo, uploaded by an admin — not in the ERD (the doc's
     * schema has no image field for DistributionPoint), added so the
     * discovery/join/tracking cards can show a real photo of the place
     * instead of always falling back to a generic stock photo per type.
     */
    public function up(): void
    {
        Schema::table('distribution_points', function (Blueprint $table) {
            $table->string('image_path')->nullable()->after('contact_phone');
        });
    }

    public function down(): void
    {
        Schema::table('distribution_points', function (Blueprint $table) {
            $table->dropColumn('image_path');
        });
    }
};
