<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    protected $fillable = ['name'];

    /**
     * Get the conversations for the project.
     * 
     * @return Conversations
     */
    public function conversations(): HasMany
    {
        return $this->hasMany(Conversation::class);
    }
}
