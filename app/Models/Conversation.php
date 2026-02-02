<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Represents a conversation thread inside a project.
 */
final class Conversation extends Model
{
	use HasFactory;

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array<int, string>
	 */
	protected $fillable = [
		'project_id',
		'title',
		'status',
		'last_message_at',
		'share_url',
		'original_url',
		'chatgpt_id',
	];

	/**
	 * The attributes that should be cast to native types.
	 *
	 * @var array<string, string>
	 */
	protected $casts = [
		'last_message_at' => 'datetime',
	];

	/**
	 * Access the project this conversation belongs to.
	 */
	public function project(): BelongsTo
	{
		// Keep relationship explicit for clarity and eager loading.
		return $this->belongsTo(Project::class);
	}

	/**
	 * Access the messages that belong to this conversation.
	 */
	public function messages(): HasMany
	{
		// Keep relationship explicit so message ordering stays consistent.
		return $this->hasMany(Message::class);
	}
}
