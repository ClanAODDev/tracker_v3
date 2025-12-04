<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        \App\Models\User::whereRaw("JSON_EXTRACT(settings, '$.snow') = false")->get()->each(function ($user) {
            $settings = $user->settings;
            $settings['snow'] = 'no_snow';
            $user->settings = $settings;
            $user->save();
        });
    }

    public function down(): void
    {
        \App\Models\User::whereRaw("JSON_EXTRACT(settings, '$.snow') = 'no_snow'")->get()->each(function ($user) {
            $settings = $user->settings;
            $settings['snow'] = false;
            $user->settings = $settings;
            $user->save();
        });
    }
};
