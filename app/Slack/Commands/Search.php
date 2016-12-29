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

    private $profile_path = "http://www.clanaod.net/forums/member.php?u=";

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

        $this->members = Member::where('name', 'LIKE', "%{$this->params}%")->get();

        if ($this->members) {
            foreach ($this->members as $member) {
                $division = ($member->primaryDivision)
                    ? "{$member->primaryDivision->name} Division"
                    : "Ex-AOD";

                $this->content[] = [
                    'title' => "{$member->present()->rankName} - {$division}",
                    'text' => $this->profile_path . $member->clan_id,
                    'color' => '#88C53E',
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
