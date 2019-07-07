<?php

namespace App\Presenters;

use App\Member;
use Carbon\Carbon;

class MemberPresenter extends Presenter
{
    /**
     * @var Member
     */
    public $member;

    /**
     * MemberPresenter constructor.
     *
     * @param Member $member
     */
    public function __construct(Member $member)
    {
        $this->member = $member;
    }

    public function lastPromoted($emptyVal = 'Never')
    {
        return !$this->member->last_promoted_at
            ? $emptyVal ?? 'Never'
            : $this->member->last_promoted_at->format('Y-m-d');
    }

    public function lastActive($value)
    {
        $value = $value instanceof Carbon ? $value : Carbon::parse($value);

        if (!$value) {
        }

//        if ($value->diffInDays() < 1) {
//            return "Today";
//        }

        return $value->diffForHumans();
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
            $title = ($this->member->position->name) ?: null;

            $icon = ($this->member->position->icon)
                ? "<i class=\"{$this->member->position->icon}\"></i>"
                : null;

            $name = ($showRank)
                ? $this->rankName()
                : $this->member->name;

            return "<span title=\"{$title}\" class=\"{$this->member->position->class}\">{$icon} {$name}</span>";
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
        if ($this->member->rank_id === 14) {
            return $this->member->name;
        }

        return $this->member->rank->abbreviation . " " . $this->member->name;
    }
}
