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
                foreach ($settings as $key => $value) {
                    if (str_starts_with($key, 'voice_alert_')) {
                        $settings[$key] = 'officers';
                    }
                }

                DB::table('divisions')->where('id', $division->id)->update([
                    'settings' => json_encode($settings, JSON_UNESCAPED_UNICODE),
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
