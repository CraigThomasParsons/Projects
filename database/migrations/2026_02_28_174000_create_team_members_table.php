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
        Schema::create('team_members', function (Blueprint $table) {
            $table->id();
            
            $table->string('name');
            $table->string('role_title');
            $table->string('one_liner');
            $table->text('bio')->nullable();
            
            $table->string('profile_image_path')->nullable();
            
            $table->text('responsibilities')->nullable();
            $table->text('strengths')->nullable();
            $table->text('limitations')->nullable();
            $table->text('tools_used')->nullable();
            
            $table->string('status')->default('Active');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('team_members');
    }
};
