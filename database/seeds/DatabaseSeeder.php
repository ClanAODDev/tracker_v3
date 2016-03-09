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
        DB::table('division_member')->insert(
            [
                ['division_id' => 1, 'member_id' => 1, 'primary' => true],
                ['division_id' => 1, 'member_id' => 2, 'primary' => true],
                ['division_id' => 1, 'member_id' => 3, 'primary' => true],
            ]
        );
    }
}
