<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Project extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'project_uuid',
        'name',
        'description',
        'code_folder',
        'local_location',
        'github_repo',
        'gitea_location',
        'framework_description',
        'languages',
    ];

    protected $casts = [
        'deleted_at' => 'datetime',
    ];

    /**
     * Assign immutable UUID identity on first create.
     */
    protected static function booted(): void
    {
        static::creating(function (Project $project): void {
            if (empty($project->project_uuid)) {
                $project->project_uuid = (string) Str::uuid();
            }
        });
    }

    /**
     * Get the conversations for the project.
     */
    public function conversations(): HasMany
    {
        return $this->hasMany(Conversation::class);
    }
}
