<?php

namespace Database\Seeders;

use App\Models\Census;
use App\Models\Division;
use App\Models\Member;
use App\Models\MemberHandle;
use App\Models\Platoon;
use App\Models\Squad;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ClanSeeder extends Seeder
{
    public function run()
    {
        if (app()->environment('production')) {
            $this->command->error('This seeder should not be run in production!');

            return;
        }

        DB::transaction(function () {
            $divisions = Division::factory()->count(2)->create();

            $divisions->each(function ($division) {
                $this->generateDivisionLeadership($division);
                $this->generateDivisionMembers($division);
                $this->generateCensusData($division);

                $partTimeMembers = Member::factory()->count(2)->create([
                    'division_id' => $division->id,
                ]);
                $division->partTimeMembers()->attach($partTimeMembers->pluck('id')->toArray());
            });

            $adminMember = Member::inRandomOrder()->first();
            if ($adminMember) {
                $adminUser = User::factory()->create([
                    'name'      => $adminMember->name,
                    'member_id' => $adminMember->id,
                    'role_id'   => 5,
            $adminMember = Member::inRandomOrder()->first();
            if ($adminMember) {
                $adminUser = User::factory()->create([
                    'name' => $adminMember->name,
                    'member_id' => $adminMember->id,
                    'role_id' => 5,
                ]);
                $this->command->info("Created Admin user: {$adminUser->name} (Member ID: {$adminUser->member_id})");
            }

            $leadershipMember = Member::whereIn('position', ['commander', 'executive_officer'])
                ->inRandomOrder()->first();
            if ($leadershipMember) {
                $srLdrDivisionUser = User::factory()->create([
                    'name'      => $leadershipMember->name,
                    'member_id' => $leadershipMember->id,
                    'role_id'   => 4,
                ]);
                $this->command->info("Created Senior Leader (Division CO/XO) user: {$srLdrDivisionUser->name} (Member ID: {$srLdrDivisionUser->member_id}, Position: {$leadershipMember->position})");
            }

            $randomPlatoon = Platoon::inRandomOrder()->first();
            if ($randomPlatoon) {
                $platoonLeader = Member::factory()->ofTypePlatoonLeader()->create([
                    'division_id' => $randomPlatoon->division_id,
                    'platoon_id'  => $randomPlatoon->id,
                ]);
                $srLdrPlatoonUser = User::factory()->create([
                    'name'      => $platoonLeader->name,
                    'member_id' => $platoonLeader->id,
                    'role_id'   => 4,
                ]);
                $this->command->info("Created Senior Leader (Platoon Leader) user: {$srLdrPlatoonUser->name} (Member ID: {$srLdrPlatoonUser->member_id})");
            }

            $randomSquad = Squad::inRandomOrder()->first();
            if ($randomSquad) {

                $platoon = $randomSquad->platoon;
                $division_id = $platoon ? $platoon->division_id : null;

                $squadLeader = Member::factory()->ofTypeSquadLeader()->create([
                    'division_id' => $division_id,
                    'platoon_id'  => $randomSquad->platoon_id,
                    'squad_id'    => $randomSquad->id,
                ]);
                $officerUser = User::factory()->create([
                    'name'      => $squadLeader->name,
                    'member_id' => $squadLeader->id,
                    'role_id'   => 2,
                ]);
                $this->command->info("Created Officer (Squad Leader) user: {$officerUser->name} (Member ID: {$officerUser->member_id})");
                $this->command->info("Created Admin user: {$adminUser->name} (Member ID: {$adminUser->member_id})");
            }

            $leadershipMember = Member::whereIn('position', ['commander', 'executive_officer'])
                ->inRandomOrder()->first();
            if ($leadershipMember) {
                $srLdrDivisionUser = User::factory()->create([
                    'name' => $leadershipMember->name,
                    'member_id' => $leadershipMember->id,
                    'role_id' => 4,
                ]);
                $this->command->info("Created Senior Leader (Division CO/XO) user: {$srLdrDivisionUser->name} (Member ID: {$srLdrDivisionUser->member_id}, Position: {$leadershipMember->position})");
            }

            $randomPlatoon = Platoon::inRandomOrder()->first();
            if ($randomPlatoon) {
                $platoonLeader = Member::factory()->ofTypePlatoonLeader()->create([
                    'division_id' => $randomPlatoon->division_id,
                    'platoon_id' => $randomPlatoon->id,
                ]);
                $srLdrPlatoonUser = User::factory()->create([
                    'name' => $platoonLeader->name,
                    'member_id' => $platoonLeader->id,
                    'role_id' => 4,
                ]);
                $this->command->info("Created Senior Leader (Platoon Leader) user: {$srLdrPlatoonUser->name} (Member ID: {$srLdrPlatoonUser->member_id})");
            }

            $randomSquad = Squad::inRandomOrder()->first();
            if ($randomSquad) {

                $platoon = $randomSquad->platoon;
                $division_id = $platoon ? $platoon->division_id : null;

                $squadLeader = Member::factory()->ofTypeSquadLeader()->create([
                    'division_id' => $division_id,
                    'platoon_id' => $randomSquad->platoon_id,
                    'squad_id' => $randomSquad->id,
                ]);
                $officerUser = User::factory()->create([
                    'name' => $squadLeader->name,
                    'member_id' => $squadLeader->id,
                    'role_id' => 2,
                ]);
                $this->command->info("Created Officer (Squad Leader) user: {$officerUser->name} (Member ID: {$officerUser->member_id})");
            }
        });
    }

    protected function generateCensusData($division): void
    {
        for ($week = 1; $week <= 6; $week++) {
            Census::factory()->create([
                'division_id' => $division->id,
                'created_at' => now()->subWeeks($week),
            ]);
        }
    }

    protected function generateDivisionLeadership($division): void
    {
        Member::factory()->ofTypeCommander()->create([
            'division_id' => $division->id,
        ]);

        Member::factory()->count(2)->ofTypeExecutiveOfficer()->create([
            'division_id' => $division->id,
        ]);
    }

    protected function generateDivisionMembers($division): void
    {

        $platoons = Platoon::factory()->count(rand(2, 5))->create([
            'division_id' => $division->id,
        ]);

        foreach ($platoons as $platoon) {

            $squads = Squad::factory()->count(rand(1, 3))->create([
                'platoon_id' => $platoon->id,
            ]);

            foreach ($squads as $squad) {

                $members = Member::factory()->ofTypeMember()->count(rand(5, 20))->create([
                    'division_id' => $division->id,
                    'platoon_id' => $platoon->id,
                    'squad_id' => $squad->id,
                ]);

                $members->each(function ($member) {
                    MemberHandle::factory()->create([
                        'member_id' => $member->id,
                    ]);
                });
            }
        }
    }
}
