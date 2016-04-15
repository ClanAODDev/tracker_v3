<?php
/**
 * Created by PhpStorm.
 * User: dcdeaton
 * Date: 4/12/2016
 * Time: 7:02 PM
 */

namespace App\Slack\Commands;


use App\Member;

class Search extends Base implements Command
{

    private $params;
    private $members;
    private $content;

    private $profile_path = "http://www.clanaod.net/forums/member.php?u=";

    /**
     * Search constructor.
     * @param $data
     */
    public function __construct($data)
    {
        $params = last(
            explode(':', $data['text'], 2)
        );

        $this->params = $params;
    }

    /**
     * @return mixed
     */
    public function handle()
    {
        $this->members = Member::where(
            'name', 'LIKE', "%{$this->params}%"
        )->get();

        if ($this->members) {
            foreach ($this->members as $member) {
                $this->content .= "{$member->name} : {$this->profile_path}{$member->clan_id} \r\n";
            }
        }

        return $this->response();
    }


    /**
     * Provide a response to slack.
     *
     * @return mixed
     */
    public function response()
    {
        if (count($this->members)) {
            return [
                'text' => "The following members were found",
                'attachments' => [
                    [
                        'text' => $this->content,
                    ],
                ],
            ];
        }

        return [
            'text' => "No results were found",
        ];
    }
}

