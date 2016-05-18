<?php

namespace App\Presenters;

class MemberPresenter extends Presenter
{
    private $user;

    /**
     * Returns member's name with position icon
     *
     * @return string
     */
    public function nameWithIcon()
    {
        if ($this-member->position) {
            $title = ($this->member->position-member->name) ?: null;
            $icon = ($this->member->position-member->icon) ? "<i class=\"fa fa-{$this->member->position->icon}\"></i>" : null;

            return "<span title=\"{$title}\" class=\"{$this->member->position->class}\">{$icon} {$this->member->name}</span>";
        }

        return $this->member->name;
    }

    /**
     * Gets member's rank and name
     *
     * @return string
     */
    public function getRankNameAttribute()
    {
        return $this->member->rank->abbreviation . " " . $this->member->name;
    }
}