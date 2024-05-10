<?php

namespace App\Presenters;

use App\Models\Member;
use Carbon\Carbon;

class MemberPresenter extends Presenter
{
    /**
     * @var Member
     */
    public $member;

    /**
     * MemberPresenter constructor.
     */
    public function __construct(Member $member)
    {
        $this->member = $member;
    }

    /**
     * TODO - Figure out what the hell this is.
     *
     * @param  mixed  $value
     */
    public function lastActive($value)
    {
        if (is_null($value)) {
            return 'Never';
        }

        $value = $value instanceof \Carbon\Carbon
            ? $value
            : \Carbon\Carbon::parse($value);

        $epochStart = Carbon::createFromTimestampUTC(0);

        if ($epochStart->equalTo($value)) {
            return 'Never';
        }

        return $value->diffForHumans();
    }

    /**
     * Returns member's name with position icon.
     *
     * @param  bool  $showRank
     * @return string
     */
    public function nameWithIcon($showRank = false)
    {
        if ($this->member->position) {
            $title = $this->member->position->name ?: null;
            $icon = $this->member->position->icon ? "<i class=\"{$this->member->position->icon}\"></i>" : null;
            $name = $showRank ? $this->rankName() : $this->member->name;

            return "<span title=\"{$title}\" class=\"{$this->member->position->class}\">{$icon} {$name}</span>";
        }

        return $this->member->name;
    }

    /**
     * Gets member's rank and name.
     *
     * @return string
     */
    public function rankName()
    {
        if ($this->member->rank_id === 14) {
            return $this->member->name;
        }

        return $this->member->rank->abbreviation . ' ' . $this->member->name;
    }
}
