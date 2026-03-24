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
        Schema::create('inception_personas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inception_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('goals')->nullable();
            $table->text('frustrations')->nullable();
            $table->text('context')->nullable();
            $table->string('tech_level')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inception_personas');
    }
};
