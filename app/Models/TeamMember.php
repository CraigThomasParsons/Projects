<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Represents a digital agent on the Factory Workbench Auto Pipeline team.
 *
 * This model defines the bounded persona of an agent, explicitly listing out
 * what they are responsible for and their rigid limitations.
 */
class TeamMember extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'role_title',
        'one_liner',
        'bio',
        'profile_image_path',
        'responsibilities',
        'strengths',
        'limitations',
        'tools_used',
        'status',
    ];
}
