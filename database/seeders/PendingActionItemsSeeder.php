<?php

namespace Database\Seeders;

use App\Models\Division;
use Illuminate\Database\Seeder;
use Tests\Traits\CreatesPendingActionItems;

class PendingActionItemsSeeder extends Seeder
{
    use CreatesPendingActionItems;

    public function run(): void
    {
        $division = Division::where('active', true)->first();

        if (! $division) {
            $this->command->error('No active division found');

            return;
        }

        $this->command->info("Using division: {$division->name}");

        $this->createInactiveMembers($division);
        $this->command->info('Created 2 inactive members');

        $this->createDivisionAwardRequests($division);
        $this->command->info('Created 2 division award requests');

        $this->createClanAwardRequests();
        $this->command->info('Created 2 clan award requests');

        $this->createPendingTransfers($division);
        $this->command->info('Created 2 pending transfers');

        $this->createPendingLeaves($division);
        $this->command->info('Created 2 pending leaves');

        $this->createVoiceIssues($division);
        $this->command->info('Created 2 members with voice issues');

        $this->createUnassignedMembers($division);
        $this->command->info('Created 2 unassigned members (no platoon)');

        $this->createMembersWithoutSquad($division);
        $this->command->info('Created 2 members without squad');

        $this->createOpenTickets();
        $this->command->info('Created 2 open tickets');
    }
}
