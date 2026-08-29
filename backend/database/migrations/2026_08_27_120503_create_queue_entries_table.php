<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('queue_entries', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('queue_id')->constrained('queues')->cascadeOnDelete();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedInteger('ticket_number');
            // waiting, called, served, no_show, skipped, cancelled
            $table->string('status', 20)->default('waiting');
            $table->boolean('priority_flag')->default(false);
            $table->foreignUuid('counter_id')->nullable()->constrained('counters')->nullOnDelete();
            $table->timestamp('joined_at')->useCurrent();
            $table->timestamp('called_at')->nullable();
            $table->timestamp('served_at')->nullable();
            $table->timestamp('left_at')->nullable();
            $table->timestamp('notified_at')->nullable();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->unique(['queue_id', 'ticket_number']);
            $table->index(['queue_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('queue_entries');
    }
};
