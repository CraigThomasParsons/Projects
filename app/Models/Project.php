<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Represents a project container that groups related conversations.
 */
final class Project extends Model
{
	use HasFactory;

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array<int, string>
	 */
	protected $fillable = [
		'name',
		'description',
		'status',
		'last_activity_at',
	];

	/**
	 * The attributes that should be cast to native types.
	 *
	 * @var array<string, string>
	 */
	protected $casts = [
		'last_activity_at' => 'datetime',
	];

	/**
	 * Access the conversations that belong to this project.
	 */
	public function conversations(): HasMany
	{
		// Keep relationship explicit to support eager loading and ordering.
		return $this->hasMany(Conversation::class);
	}
}
