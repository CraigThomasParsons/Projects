<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InceptionFeature extends Model
{
    protected $fillable = [
        'inception_id',
        'title',
        'description',
        'value_score',
        'effort_score',
        'mvp_status',
    ];

    public function inception(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Inception::class);
    }
}
