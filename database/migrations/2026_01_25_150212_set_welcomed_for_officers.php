<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        User::whereHas('member', fn ($q) => $q->where('rank', '>=', 9))
            ->each(function ($user) {
                $settings = $user->settings ?? [];
                $settings['welcomed'] = true;
                $user->settings = $settings;
                $user->save();
            });
    }

    public function down(): void
    {
        User::whereHas('member', fn ($q) => $q->where('rank', '>=', 9))
            ->each(function ($user) {
                $settings = $user->settings ?? [];
                unset($settings['welcomed']);
                $user->settings = $settings;
                $user->save();
            });
    }
};
