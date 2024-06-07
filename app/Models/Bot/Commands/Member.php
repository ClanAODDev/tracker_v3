<?php

namespace App\Models\Bot\Commands;

use App\Models\Bot\Base;
use App\Models\Bot\Command;

/**
 * Class Search.
 */
class Member extends Base implements Command
{
    public function __construct($request)
    {
        parent::__construct($request);

        $validated = $this->request->validate([
            'field' => 'required|in:name,discord,ts_unique_id',
            'value' => 'required|min:3',
        ]);
    }

    /**
     * @return array
     */
    public function handle()
    {
        $members = \App\Models\Member::where($this->params['field'], 'LIKE', "%{$this->params['value']}%")->get();

        // count before iterating
        if ($members->count() > 10) {
            return [
                'message' => 'More than 10 members were found. Please narrow your search terms.',
            ];
        }

        if ($members) {
            foreach ($members as $member) {
                $content[] = $member->botResponse();
            }
        }

        if ($members->count() >= 1) {
            return [
                'embed' => [
                    'color' => 10181046,
                    'title' => 'The following members were found:',
                    'fields' => $content,
                ],
            ];
        }

        return [
            'message' => 'No results were found',
        ];
    }
}
