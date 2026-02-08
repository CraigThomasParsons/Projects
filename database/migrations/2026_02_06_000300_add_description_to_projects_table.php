<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add project descriptions.
     */
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            // Store an optional description so projects can convey intent.
            $table->text('description')->nullable()->after('name');
        });
    }

    /**
     * Roll back the description column.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn('description');
        });
    }
};
