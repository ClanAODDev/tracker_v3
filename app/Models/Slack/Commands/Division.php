<?php

namespace App\Models\Slack\Commands;

use App\Models\Slack\Base;
use App\Models\Slack\Command;

/**
 * Class Search
 *
 * @package App\Slack\Commands
 */
class Division extends Base implements Command
{

    public function __construct($data)
    {
        parent::__construct($data);

        $this->request = $data;
    }

    /**
     * @return array
     */
    public function handle()
    {
        if (strlen($this->params) >= 5) {
            return [
                'text' => "Please provide the division abbreviation - not the name!",
            ];
        }

        $division = \App\Models\Division::where('abbreviation', $this->params)->first();

        $leaderData = "";

        if ($division) {
            foreach ($division->leaders()->get() as $leader) {
                $leaderData .= $leader->present()->rankName() . ' - ' . $leader->position->name . PHP_EOL;
            }

            return [
                "embed" => [
                    'color' => 10181046,
                    'title' => "Search results",
                    'fields' => [
                        'name' => "https://tracker.clanaod.net/images/game_icons/48x48/al.png {$division->name}",
                        'value' => $leaderData
                    ]
                ]
            ];
        }

        return [
            'text' => "No results were found",
        ];
    }

    /**
     * @param $member
     * @return string|null
     */
    private function buildActivityBlock($member)
    {
        $forumActivity = $member->last_activity->diffForHumans();

        return PHP_EOL . "Forum activity: {$forumActivity}";
    }
}
