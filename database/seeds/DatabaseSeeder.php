<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        // seed members
        factory(App\Member::class, 10)->create();

        // seed platoons and squads
        factory(App\Platoon::class, 3)->create()->each(function ($p) {
            $p->squads()->save(factory(App\Squad::class)->make());
        });

        // generate division members
        DB::table('division_member')->insert(
            [
                ['division_id' => 1, 'member_id' => 1, 'primary' => true],
                ['division_id' => 1, 'member_id' => 2, 'primary' => true],
                ['division_id' => 1, 'member_id' => 3, 'primary' => true],
            ]
        );
    }
}
