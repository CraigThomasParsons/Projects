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
        Schema::create('conversations', function (Blueprint $table) {
            $table->id();
            // Link conversations back to the owning project container.
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            // Give each conversation a clear, human title for navigation.
            $table->string('title');
            // Track lightweight lifecycle state without heavy workflow rules.
            $table->string('status')->default('active');
            // Provide a quick resume hint without scanning messages.
            $table->timestamp('last_message_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conversations');
    }
};
