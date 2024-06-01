<?php

namespace App\Models\Bot\Commands;

use App\Models\Bot\Base;
use App\Models\Bot\Command;

/**
 * Class Search.
 */
class Division extends Base implements Command
{
    public function __construct($request)
    {
        parent::__construct($request);
    }

    /**
     * @return array
     */
    public function handle()
    {
        $division = \App\Models\Division::where('abbreviation', $this->params['query'])
            ->orWhere('name', $this->params['query'])->first();

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
            'message' => 'No results were found',
        ];
    }
}
