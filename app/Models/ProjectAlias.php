<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectAlias extends Model
{
    protected $fillable = [
        'project_id',
        'alias',
    ];

    /**
     * Get the project that owns the alias.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
