<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Add immutable project UUID and soft-delete support for registry syncing.
     */
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->uuid('project_uuid')->nullable()->after('id');
            $table->softDeletes();
        });

        // Backfill UUID values so all existing projects are addressable cross-service.
        DB::table('projects')
            ->orderBy('id')
            ->get(['id'])
            ->each(function ($project): void {
                DB::table('projects')
                    ->where('id', $project->id)
                    ->update(['project_uuid' => (string) Str::uuid()]);
            });

        Schema::table('projects', function (Blueprint $table) {
            $table->unique('project_uuid');
        });
    }

    /**
     * Remove registry columns.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropUnique(['project_uuid']);
            $table->dropColumn('project_uuid');
            $table->dropSoftDeletes();
        });
    }
};
