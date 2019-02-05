<?php
/**
 * Created by PhpStorm.
 * User: dcdeaton
 * Date: 4/12/2016
 * Time: 7:02 PM
 */

namespace App\Slack\Commands;

use App\Member;
use App\Slack\Base;
use App\Slack\Command;

class Search extends Base implements Command
{

    private $members;

    private $content = [];

    private $searchingByDiscord = false;

    private $forumProfile = "https://www.clanaod.net/forums/member.php?u=";

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
                'text' => "Your search criteria must be 3 characters or more",
            ];
        }


        // are we handling multiple names?
        if (strstr($this->params, ',')) {
            $criteria = [];
            $terms = explode(',', $this->params);

            if (count($terms) > 2) {
                return [
                    'text' => "Your search criteria can only be a maximum of two terms.",
                ];
            }

            $this->members = Member::where('name', 'LIKE', "%{$terms[0]}%")
                ->orWhere('name', 'LIKE', "%{$terms[1]}%")
                ->get();
        } else {
            if (strstr($this->params, 'discord')) {
                $this->searchingByDiscord = true;
                $value = str_replace(' discord', '', $this->params);
                $this->members = Member::where('discord', 'LIKE', "%{$value}%")->get();
            } else {
                $this->members = Member::where('name', 'LIKE', "%{$this->params}%")->get();
            }
        }

        // count before iterating
        if ($this->members->count() > 10) {
            return [
                'text' => 'More than 10 members were found. Please narrow your search terms.'
            ];
        }

        if ($this->members) {
            foreach ($this->members as $member) {
                $division = ($member->division)
                    ? "{$member->division->name} Division"
                    : "Ex-AOD";

                $memberLink = route('member', $member->getUrlParams());

                $links = [
                    "[Forum]({$this->forumProfile}{$member->clan_id})",
                    "[Tracker]({$memberLink})"
                ];

                $this->content[] = [
                    'name' => "{$member->present()->rankName} ({$member->clan_id}) - {$division}",
                    'value' => "Profiles: "
                    . implode(', ', $links)
                    . $this->buildActivityBlock($member)
                    . ($this->searchingByDiscord) ? $this->buildDiscordBlock($member) : null

                ];
            }
        }

        if ($this->members->count() >= 1) {
            return [
                "embed" => [
                    'color' => 10181046,
                    'title' => 'The following members were found:',
                    'fields' => $this->content
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

    /**
     * @param $member
     * @return string
     */
    private function buildDiscordBlock($member)
    {
        return PHP_EOL . "Discord: `{$member->discord}`";
    }
}
