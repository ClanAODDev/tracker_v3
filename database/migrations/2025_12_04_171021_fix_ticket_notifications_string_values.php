<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        \App\Models\User::all()->each(function ($user) {
            $settings = $user->settings;

            if (isset($settings['ticket_notifications'])) {
                $value = $settings['ticket_notifications'];

                if ($value === 'on' || $value === '1' || $value === 1) {
                    $settings['ticket_notifications'] = true;
                } elseif ($value === 'off' || $value === '0' || $value === 0) {
                    $settings['ticket_notifications'] = false;
                } elseif (! is_bool($value)) {
                    $settings['ticket_notifications'] = true;
                }

                $user->settings = $settings;
                $user->save();
            }
        });
    }

    public function down(): void {}
};
