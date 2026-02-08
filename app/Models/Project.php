<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    protected $fillable = ['name', 'description'];

    /**
     * Get the conversations for the project.
     */
    public function conversations(): HasMany
    {
        return $this->hasMany(Conversation::class);
    }
}
