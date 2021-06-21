<?php

namespace Database\Seeders;

use App\Models\Census;
use App\Models\Division;
use App\Models\Member;
use App\Models\Platoon;
use App\Models\Squad;
use App\Models\User;
use Illuminate\Database\Seeder;

class ClanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // generate divisions
        Division::factory()->count(3)->create();

        foreach (Division::all() as $division) {
            $this->command->info("Adding and populating a division - {$division->name}");

            $this->generateDivisionLeadership($division);
            $this->generateDivisionMembers($division);
            $this->generateCensusData($division);
        }

        // generate user
        $member = Member::inRandomOrder()->first();
        User::factory()->create([
            'name' => $member->name,
            'member_id' => $member,
            'role_id' => 5
        ]);
    }

    /**
     * @param $division
     */
    protected function generateCensusData($division): void
    {
        for ($i = 1;$i < 7; $i++) {
            Census::factory()->create([
                'division_id' => $division,
                'created_at' => now()->subWeeks($i),
            ]);
        }
    }

    /**
     * @param $division
     */
    protected function generateDivisionLeadership($division): void
    {
        // a commander
        Member::factory()->commander()->create([
            'division_id' => $division,
        ]);

        // some XOs
        Member::factory()->count(2)->executiveOfficer()->create([
            'division_id' => $division,
        ]);
    }

    private function generateDivisionMembers($division)
    {
        $platoons = Platoon::factory()->count(rand(2, 5))->create([
            'division_id' => $division,
        ]);

        foreach ($platoons as $platoon) {
            $squads = Squad::factory()->count(rand(1, 3))->create([
                'platoon_id' => $platoon,
            ]);

            foreach ($squads as $squad) {
                Member::factory()->member()->count(rand(5, 20))->create([
                    'division_id' => $division,
                    'platoon_id' => $platoon,
                    'squad_id' => $squad,
                ]);
            }
        }
    }
}
