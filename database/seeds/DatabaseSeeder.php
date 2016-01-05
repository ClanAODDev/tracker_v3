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
        $faker = Faker\Factory::create();

        foreach (range(1, 3) as $index) {

            DB::table('members')->insert([
                'name' => $faker->userName,
                'clan_id' => $faker->randomNumber(6),
                'rank_id' => $faker->numberBetween(1, 14),
                'platoon_id' => $faker->numberBetween(1, 3),
                'position_id' => $faker->numberBetween(1, 11),
                'squad_id' => $faker->numberBetween(1, 3),
                'join_date' => $faker->dateTime('now'),
                'last_forum_login' => $faker->dateTime('now'),
            ]);

            DB::table('platoons')->insert([
                'order' => $faker->numberBetween(1, 3),
                'name' => $faker->name,
                'division_id' => 1,
                'platoon_leader_id' => $faker->randomNumber(5)
            ]);

            DB::table('squads')->insert([
                'platoon_id' => $faker->numberBetween(1, 3),
                'squad_leader_id' => $faker->randomNumber(5)
            ]);

        }

        DB::table('division_member')->insert(
            [
                ['division_id' => 1, 'member_id' => 1, 'primary' => true],
                ['division_id' => 1, 'member_id' => 2, 'primary' => true],
                ['division_id' => 1, 'member_id' => 3, 'primary' => true],
            ]
        );
    }
}
