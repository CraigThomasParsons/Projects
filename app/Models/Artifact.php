<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Artifact extends Model
{
    protected $fillable = [
        'project_id',
        'kind',
        'path',
        'content',
        'content_hash',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function artifactable(): MorphTo
    {
        return $this->morphTo();
    }
}
