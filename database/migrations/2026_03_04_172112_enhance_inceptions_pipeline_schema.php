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
        Schema::table('inceptions', function (Blueprint $table) {
            $table->json('mvp_canvas')->nullable()->after('vision_statement');
            $table->dateTime('started_at')->nullable()->after('status');
            $table->dateTime('completed_at')->nullable()->after('started_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inceptions', function (Blueprint $table) {
            $table->dropColumn(['mvp_canvas', 'started_at', 'completed_at']);
        });
    }
};
