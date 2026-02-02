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
        Schema::create('message_checkboxes', function (Blueprint $table) {
            $table->id();
            // Keep checkboxes tied to the message they were parsed from.
            $table->foreignId('message_id')->constrained()->cascadeOnDelete();
            // Store the original order index to keep toggles stable.
            $table->unsignedInteger('position_index');
            // Store the label text for display and quick search.
            $table->string('label');
            // Track the current toggle state without mutating markdown.
            $table->boolean('is_checked')->default(false);
            $table->timestamps();

            // Prevent duplicate checkbox slots within a single message.
            $table->unique(['message_id', 'position_index']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('message_checkboxes');
    }
};
