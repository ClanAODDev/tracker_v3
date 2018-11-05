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

    private $forumProfile = "https://www.clanaod.net/forums/member.php?u=";

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
            $this->members = Member::where('name', 'LIKE', "%{$this->params}%")->get();
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
                    'title' => "{$member->present()->rankName} ({$member->clan_id}) - {$division}",
                    'text' => "Profiles: " . implode(', ', $links),
                    'color' => ($member->division) ? '#88C53E' : '#ff0000',
                ];
            }
        }

        if ($this->members->count() > 10) {
            return [
                'text' => 'More than 10 members were found. Please narrow your search terms.'
            ];
        }

        if ($this->members->count() >= 1) {
            return [
                'response_type' => 'in_channel',
                'text' => "The following members were found:",
                'attachments' => $this->content,
            ];
        }

        return [
            'text' => "No results were found",
        ];
    }
}
