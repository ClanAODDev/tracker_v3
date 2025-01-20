<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('divisions')->get(['id', 'settings'])->each(function ($division) {
            $settings = json_decode($division->settings, true);

            if ($settings) {
                $updatedSettings = [];
                $memberChannelExists = false;

                foreach ($settings as $key => $value) {
                    if (str_starts_with($key, 'slack_alert_')) {
                        $newKey = str_replace('slack', 'voice', $key);
                        $updatedSettings[$newKey] = $value;
                    } elseif ($key === 'slack_channel') {
                        $updatedSettings['member_channel'] = $value;
                        $memberChannelExists = true;
                    } elseif (! in_array($key, ['use_discord_activity', 'voice_alert_updated_member'])) {
                        // nuke some settings we no longer need
                        $updatedSettings[$key] = $value;
                    }
                }

                if (! $memberChannelExists) {
                    $updatedSettings['member_channel'] = null;
                }

                $updatedSettings['voice_alert_rank_changed'] = false;

                DB::table('divisions')->where('id', $division->id)->update([
                    'settings' => json_encode($updatedSettings, JSON_UNESCAPED_UNICODE),
                ]);
            }
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
