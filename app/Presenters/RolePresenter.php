<?php

namespace App\Presenters;

use App\Role;

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
            case 'jr_ldr':
                return "<span class=\"text-info\">{$this->role->label}</span>";
        }

        return ucwords($this->role->name);
    }
}