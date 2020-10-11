<?php

namespace App\Presenters;

use App\Models\Role;

class RolePresenter extends Presenter
{
    public $role;

    public function __construct(Role $role)
    {
        $this->role = $role;
    }

    public function roleLabelColored()
    {
        switch ($this->role->name) {
            case 'admin':
                return "<span class=\"text-danger\">{$this->role->label}</span>";
            case 'sr_ldr':
                return "<span class=\"text-warning\">{$this->role->label}</span>";
        }

        return ucwords($this->role->name);
    }
}
