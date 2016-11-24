<?php

use App\Division;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddRecruitingSettingsAndSlackSettings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $divisions = Division::all();

        foreach ($divisions as $division) {
            $settings = $division->settings();

            // provide defaults
            $settings->set('recruiting_threads', [
                [
                    'thread_name' => 'AOD Code of Conduct',
                    'thread_id' => 3327
                ],
                [
                    'thread_name' => 'AOD Ranking Structure',
                    'thread_id' => 3326
                ],
            ]);

            $settings->set('recruiting_tasks', [
                [
                    'task_description' => 'Adjust forum profile settings'
                ],
                [
                    'task_description' => 'Copy TS identity unique id to forum profile',
                ],
                [
                    'task_description' => 'Change name on Teamspeak: %%member_name%%',
                ],
                [
                    'task_description' => 'Reminder that forum login name will change in 24/48 hours',
                ],
                [
                    'task_description' => 'Introduce new member to the other members of the division',
                ],
            ]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
