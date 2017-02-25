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
        return ! $this->member->last_promoted
            ? "Never"
            : $this->member->last_promoted->format('M d, Y');
    }

    public function lastActive()
    {
        if ($this->member->last_activity->diffInDays() < 1) {
            return "Today";
        }

        return $this->member->last_activity->diffForHumans();
    }

    /**
     * Returns member's name with position icon
     *
     * @param bool $showRank
     * @return string
     */
    public function nameWithIcon($showRank = false)
    {
        if ($this->member->position) {
            $title = ($this->member->position->name) ? : null;

            $icon = ($this->member->position->icon)
                ? "<i class=\"{$this->member->position->icon}\"></i>"
                : null;

            $rank = ($this->member->rank->abbreviation and $showRank)
                ? $this->member->rank->abbreviation
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
