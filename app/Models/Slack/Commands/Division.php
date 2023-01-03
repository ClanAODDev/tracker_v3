<?php

namespace App\Models\Slack\Commands;

use App\Models\Slack\Base;
use App\Models\Slack\Command;

/**
 * Class Search.
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
        if (\strlen($this->params) >= 5) {
            return [
                'text' => 'Please provide the division abbreviation - not the name!',
            ];
        }

        $division = \App\Models\Division::where('abbreviation', $this->params)->first();

        $leaderData = '';

        if ($division) {
            foreach ($division->leaders()->get() as $leader) {
                $leaderData .= $leader->present()->rankName() . ' - ' . $leader->position->name . PHP_EOL;
            }

            return [
                'embed' => [
                    'color' => 10181046,
                    'author' => [
                        'name' => $division->name,
                        'icon_url' => getDivisionIconPath($division->abbreviation),
                        'url' => 'https://clanaod.net/divisions/' . \Str::slug($division->name),
                    ],
                    'fields' => [
                        [
                            'name' => 'Leadership',
                            'value' => $leaderData,
                        ],
                        [
                            'name' => 'Member count',
                            'value' => $division->members()->count(),
                        ],
                    ],
                ],
            ];
        }

        return [
            'text' => 'No results were found',
        ];
    }

    /**
     * @param $member
     * @return null|string
     */
    private function buildActivityBlock($member)
    {
        $forumActivity = $member->last_activity->diffForHumans();

        return PHP_EOL . "Forum activity: {$forumActivity}";
    }
}
