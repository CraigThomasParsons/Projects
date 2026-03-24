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
        Schema::create('artifacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
            $table->morphs('artifactable'); // Creates artifactable_type and artifactable_id
            $table->string('kind', 40); // vision|mvp|personas|spec|report
            $table->string('path', 255); // e.g /inception/vision.md
            $table->longText('content')->nullable();
            $table->char('content_hash', 64)->nullable();
            $table->timestamps();
            
            $table->index(['project_id', 'kind']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('artifacts');
    }
};
