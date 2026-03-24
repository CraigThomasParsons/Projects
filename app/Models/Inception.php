<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inception extends Model
{
    protected $fillable = [
        'project_id',
        'status',
        'business_goals',
        'success_metrics',
        'vision_statement',
        'mvp_canvas',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'success_metrics' => 'array',
        'mvp_canvas' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function project(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function personas(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(InceptionPersona::class);
    }

    public function features(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(InceptionFeature::class);
    }

    public function artifacts(): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        return $this->morphMany(Artifact::class, 'artifactable');
    }
}
