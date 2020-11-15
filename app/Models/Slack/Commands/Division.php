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
        if (strlen($this->params) < 3) {
            return [
                'text' => "Provide 3 characters or more of the division name",
            ];
        }

        $division = Division::where('name', 'LIKE', "%{$this->params}%")->get();

        if ($division) {
            $leaders = $division->leaders->toArray();
            return [
                "embed" => [
                    'color' => 10181046,
                    'title' => "Search results",
                    'fields' =>  [
                        'name' => "{$division->name}",
                        'value' => $division->leaders->each()
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
