<?php

namespace App\Presenters;

use App\Member;

class MemberPresenter extends Presenter
{
    /**
     * @var Member
     */
    public $member;

    /**
     * MemberPresenter constructor.
     * @param Member $member
     */
    public function __construct(Member $member)
    {
        $this->member = $member;
    }


    /**
     * Returns member's name with position icon
     *
     * @return string
     */
    public function nameWithIcon()
    {
        if ($this->member->position) {
            $title = ($this->member->position->name) ?: null;
            $icon = ($this->member->position->icon) ? "<i class=\"fa fa-{$this->member->position->icon}\"></i>" : null;

            return "<span title=\"{$title}\" class=\"{$this->member->position->class}\">{$icon} {$this->member->name}</span>";
        }

        return $this->member->name;
    }

    /**
     * Gets member's rank and name
     *
     * @return string
     */
    public function rankName()
    {
        return $this->member->rank->abbreviation . " " . $this->member->name;
    }
}
