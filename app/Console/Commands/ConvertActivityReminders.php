<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ConvertActivityReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:convert-activity-notes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Look for notes containing keywords indicating they are for inactivity, and convert them to activity entries';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $inactivity_keywords = [
            'inactivity',
            '30 day',
            '60 day',
            '90 day',
            'reminder',
            'sent',
            'inactivity removal',
        ];

        $blacklist_keywords = [
            'removed',
            'within',
            'hey',
            'moving',
            'refreshing',
            'unsure',
            'broken',
            'apologize',
            'in the past',
            'leave request',
            'ts violation',
            'running joke',
            'recruiting etiquette',
            'not in ts',
            'not on ts',
            'not in teamspeak',
            'not on teamspeak',
            'leave of absence',
            'behavior',
            'trash talking',
            'login to ts',
            'military duties',
            'military leave',
            'promotion',
            'online tonight',
            'removal',
            'teamspeak alignment',
            'shy',
            'homophobic',
            'coc violation',
            'intoxicated',
            'good fit',
            'highly valued',
            'disrespectful',
            'disparaging',
            'raging',
        ];

        $query = \App\Models\Note::query();

        $query->select('body')->whereHas('member.division')->where(function ($query) use ($inactivity_keywords) {
            foreach ($inactivity_keywords as $keyword) {
                $query->orWhere('body', 'LIKE', '%' . $keyword . '%');
            }
        });

        foreach ($blacklist_keywords as $keyword) {
            $query->where('body', 'NOT LIKE', '%' . $keyword . '%');
        }

        $query->whereRaw('LENGTH(body) <= 200');

        $sql = $query->toSql();

        // To get the bindings (parameters)
        $bindings = $query->getBindings();

        // Display the SQL query and its bindings
        dd(vsprintf(str_replace('?', '%s', $sql), array_map(function ($binding) {
            return is_numeric($binding) ? $binding : "'$binding'";
        }, $bindings)));

        return Command::SUCCESS;
    }
}
