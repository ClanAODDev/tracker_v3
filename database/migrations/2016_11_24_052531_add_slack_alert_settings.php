<?php

use App\Division;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSlackAlertSettings extends Migration
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
            $settings->set('slack_alert_created_member', false);
            $settings->set('slack_alert_removed_member', false);
            $settings->set('slack_alert_updated_member', false);
            $settings->set('slack_alert_created_request', false);
            $settings->set('slack_webhook_url', '');
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public
    function down()
    {
        //
    }
}
