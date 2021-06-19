<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class RankSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \DB::table('ranks')->insert(
            [
                ['name' => 'Recruit', 'abbreviation' => 'Rct'],
                ['name' => 'Cadet', 'abbreviation' => 'Cdt'],
                ['name' => 'Private', 'abbreviation' => 'Pvt'],
                ['name' => 'Private first class', 'abbreviation' => 'Pfc'],
                ['name' => 'Specialist', 'abbreviation' => 'Spec'],
                ['name' => 'Trainer', 'abbreviation' => 'Tr'],
                ['name' => 'Lance Corporal', 'abbreviation' => 'LCpl'],
                ['name' => 'Corporal', 'abbreviation' => 'Cpl'],
                ['name' => 'Sergeant', 'abbreviation' => 'Sgt'],
                ['name' => 'Staff Sergeant', 'abbreviation' => 'SSgt'],
                ['name' => 'Master Sergeant', 'abbreviation' => 'MSgt'],
                ['name' => 'First Sergeant', 'abbreviation' => '1stSgt'],
                ['name' => 'Command Sergeant', 'abbreviation' => 'CmdSgt'],
                ['name' => 'Sergeant Major', 'abbreviation' => 'SgtMaj'],
            ]
        );
    }
}
