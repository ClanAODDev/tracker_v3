<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Tests\Traits\CreatesPendingActionItems;

class AdminActionItemsSeeder extends Seeder
{
    use CreatesPendingActionItems;

    public function run(): void
    {
        $this->createClanAwardRequests();
        $this->command->info('Created 2 clan award requests');

        $this->createOpenTickets();
        $this->command->info('Created 2 open tickets');
    }
}
