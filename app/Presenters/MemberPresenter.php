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

    public function lastPromoted()
    {
        if ( ! $this->member->last_promoted) {
            return "Never";
        }

        return $this->member->last_promoted->diffInDays();
    }

    public function lastActive()
    {
        if ($this->member->last_forum_login->diffInDays() < 1) {
            return "Today";
        }

        return $this->member->last_forum_login->diffInDays() . " days ago";
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
            $rank = ($this->member->rank->abbreviation) ?: null;
            $icon = ($this->member->position->icon)
                ? "<i class=\"fa fa-{$this->member->position->icon}\"></i>"
                : null;

            return "<span title=\"{$title}\" class=\"{$this->member->position->class}\">{$icon} {$rank} {$this->member->name}</span>";
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
