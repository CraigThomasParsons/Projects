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
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            // Keep messages scoped to a single conversation thread.
            $table->foreignId('conversation_id')->constrained()->cascadeOnDelete();
            // Store a simple author role to avoid premature user modeling.
            $table->string('author_role')->default('user');
            // Preserve the raw markdown content for rendering and parsing.
            $table->text('content');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
