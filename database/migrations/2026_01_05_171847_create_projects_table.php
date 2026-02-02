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
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            // Store a human-friendly name for list and navigation views.
            $table->string('name');
            // Preserve a short description without forcing rigid structure.
            $table->text('description')->nullable();
            // Track whether the project is active or paused without extra tables.
            $table->string('status')->default('active');
            // Keep a lightweight activity marker for sorting and resume context.
            $table->timestamp('last_activity_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
