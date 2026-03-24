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
        Schema::table('projects', function (Blueprint $table) {
            $table->string('type')->default('code');
            $table->longText('readme')->nullable();
            $table->longText('goals')->nullable();
            $table->longText('context')->nullable();
            $table->longText('architecture')->nullable();
            $table->longText('tys')->nullable();
            $table->longText('recommendedstack')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn([
                'type',
                'readme',
                'goals',
                'context',
                'architecture',
                'tys',
                'recommendedstack',
            ]);
        });
    }
};
