<?php

namespace App\Presenters;

use App\Models\Member;
use Carbon\Carbon;
use Carbon\CarbonInterface;

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
     * Returns activity date difference in human-readable form
     *
     * @param  array  $skipUnits  Array of difference units to skip. Ex. weeks, months, etc.
     * @return string
     *
     * @throws \Exception
     */
    public function lastActive($activityType, array $skipUnits = [])
    {
        if (! in_array($activityType, [
            'last_ts_activity',
            'last_voice_activity',
        ])) {
            throw new \Exception('Invalid activity type provided to `lastActive()`');
        }

        $value = $this->member->$activityType;

        if (is_null($value)) {
            return 'Never';
        }

        $value = $value instanceof \Carbon\Carbon
            ? $value
            : \Carbon\Carbon::parse($value);

        $epochStart = Carbon::createFromTimestamp(0);

        if ($epochStart->equalTo($value)) {
            return 'Never';
        }

        return $value->diffForHumans(['skip' => $skipUnits], CarbonInterface::DIFF_ABSOLUTE);
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
            $title = $this->member->position->getLabel() ?: null;
            $icon = "<i class=\"{$this->member->position->getIcon()}\"></i>";
            $name = $showRank ? $this->rankName() : $this->member->name;

            return "<span title=\"{$title}\" class=\"{$this->member->position->getClass()}\">{$icon} {$name}</span>";
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

        return $this->member->rank->getAbbreviation() . ' ' . $this->member->name;
    }
}
