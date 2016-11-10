<?php

use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        /**
         * Populate our roles
         */

        $date = \Carbon\Carbon::now()->toDateTimeString();

        DB::table('roles')->insert(
            [
                [
                    'name' => 'user',
                    'label' => 'User',
                    'created_at' => $date,
                    'updated_at' => $date,
                ],
                [
                    'name' => 'jr_ldr',
                    'label' => 'Junior Leader',
                    'created_at' => $date,
                    'updated_at' => $date,
                ],
                [
                    'name' => 'sr_ldr',
                    'label' => 'Senior Leader',
                    'created_at' => $date,
                    'updated_at' => $date,
                ],
                [
                    'name' => 'admin',
                    'label' => 'Administrator',
                    'created_at' => $date,
                    'updated_at' => $date,
                ],
            ]
        );

    }
}
