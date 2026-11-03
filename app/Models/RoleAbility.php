<?php

namespace App\Models;

use App\Enums\Role;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['role', 'ability'])]
class RoleAbility extends Model
{
    protected function casts(): array
    {
        return ['role' => Role::class];
    }
}
