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

            $member = Member::inRandomOrder()->first();
            if ($member) {
                User::factory()->create([
                    'name' => $member->name,
                    'member_id' => $member->id,
                    'role_id' => 5,
                ]);
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
