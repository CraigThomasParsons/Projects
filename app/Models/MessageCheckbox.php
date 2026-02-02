<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Stores a parsed markdown checkbox and its independent toggle state.
 */
final class MessageCheckbox extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'message_id',
        'position_index',
        'label',
        'is_checked',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_checked' => 'boolean',
    ];

    /**
     * Access the message this checkbox belongs to.
     */
    public function message(): BelongsTo
    {
        // Keep relationship explicit for clarity and eager loading.
        return $this->belongsTo(Message::class);
    }
}
