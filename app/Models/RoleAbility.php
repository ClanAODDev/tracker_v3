<?php

namespace App\Models;

use App\Enums\Role;
use Illuminate\Database\Eloquent\Model;

class RoleAbility extends Model
{
    protected $fillable = ['role', 'ability'];

    protected function casts(): array
    {
        return ['role' => Role::class];
    }
}
