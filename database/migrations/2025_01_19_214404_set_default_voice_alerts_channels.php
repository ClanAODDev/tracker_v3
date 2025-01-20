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

            if (! $settings) {
                $settings = [];
            }

            $settings['chat_alerts'] = [
                'member_awarded' => 'officers',
                'member_created' => 'officers',
                'member_removed' => 'officers',
                'request_created' => 'officers',
                'division_edited' => 'officers',
                'member_denied' => 'officers',
                'member_approved' => 'officers',
                'member_transferred' => 'officers',
                'pt_member_removed' => 'officers',
                'rank_changed' => 'officers',
            ];

            foreach ($settings as $key => $value) {
                if (str_starts_with($key, 'voice_alert_')) {
                    unset($settings[$key]);
                }
            }

            DB::table('divisions')->where('id', $division->id)->update([
                'settings' => json_encode($settings, JSON_UNESCAPED_UNICODE),
            ]);
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
