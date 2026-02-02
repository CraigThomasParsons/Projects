<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Represents a single markdown message within a conversation.
 */
final class Message extends Model
{
	use HasFactory;

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array<int, string>
	 */
	protected $fillable = [
		'conversation_id',
		'author_role',
		'content',
	];

	/**
	 * The attributes that should be cast to native types.
	 *
	 * @var array<string, string>
	 */
	protected $casts = [
		// Keep timestamps predictable for consistent ordering in the UI.
		'created_at' => 'datetime',
	];

	/**
	 * Access the conversation this message belongs to.
	 */
	public function conversation(): BelongsTo
	{
		// Keep relationship explicit for clarity and eager loading.
		return $this->belongsTo(Conversation::class);
	}

	/**
	 * Access the checkboxes parsed from this message.
	 */
	public function checkboxes(): HasMany
	{
		// Keep relationship explicit to enable stable checkbox ordering.
		return $this->hasMany(MessageCheckbox::class)
			->orderBy('position_index');
	}
}
