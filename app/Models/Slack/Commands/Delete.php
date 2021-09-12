<?php

namespace App\Models\Slack\Commands;

use App\Models\Slack\Base;
use App\Models\Slack\Command;

/**
 * This doesn't actually do anything. Just a gag.
 *
 * Class Division
 */
class Delete extends Base implements Command
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
        if (\strlen($this->params) <= 3) {
            return [
                'text' => 'Please provide the member ID you wish to delete',
            ];
        }

        $member = \App\Models\Member::where('clan_id', 'LIKE', "%{$this->params}%")->first();

        if (!$member) {
            return [
                'text' => "I can't delete a member that doesn't exist. Please provide AOD Clan ID.",
            ];
        }

        $responses = [
            ":skull_crossbones: I will delete {$member->name} in 5 seconds unless you cancel the command...",
            ":skull_crossbones: {$member->name} has been permanently deleted! Recovery is not possible.",
            ":skull_crossbones: Permission denied! Your attempt to delete {$member->name} has been reported to the clan admins.",
            ":skull_crossbones: Do you really think {$member->name} wants you to delete them? That's not nice.",
        ];

        return [
            'text' => $responses[array_rand($responses)],
        ];
    }
}
