<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class PendingDiscordSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()
            ->pending()
            ->count(5)
            ->create();
    }
}
