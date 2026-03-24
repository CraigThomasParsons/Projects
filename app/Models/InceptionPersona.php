<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InceptionPersona extends Model
{
    protected $fillable = [
        'inception_id',
        'name',
        'goals',
        'frustrations',
        'context',
        'tech_level',
    ];

    public function inception(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Inception::class);
    }
}
