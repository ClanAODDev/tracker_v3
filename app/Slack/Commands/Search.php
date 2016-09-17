<?php
/**
 * Created by PhpStorm.
 * User: dcdeaton
 * Date: 4/12/2016
 * Time: 7:02 PM
 */

namespace App\Slack\Commands;

use App\Slack\Base;
use App\Slack\Command;

class Search extends Base implements Command
{

    private $members;
    private $content = [];

    private $profile_path = "http://www.clanaod.net/forums/member.php?u=";

    /**
     * @return array|mixed
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
                $division = ($member->primaryDivision) ? "{$member->primaryDivision->name} Division" : null;
                $this->content[] = [
                    'title' => "{$member->present()->rankName} - {$division}",
                    'text' => $this->profile_path . $member->clan_id,
                    'color' => '#88C53E',
                ];
            }
        }

        if (count($this->members) > 10) {
            return [
                'text' => 'More than 10 members were found. Please narrow your search terms.'
            ];
        } else if (count($this->members) >= 1) {
            return [
                'text' => "The following members were found",
                'attachments' => $this->content,
            ];
        }

        return [
            'text' => "No results were found",
        ];
    }
}
