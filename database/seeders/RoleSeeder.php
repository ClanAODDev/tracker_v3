<?php

namespace Database\Seeders;

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

        \DB::table('roles')->insert(
            [
                [
                    'name' => 'member',
                    'label' => 'Member',
                    'created_at' => $date,
                    'updated_at' => $date,
                ],
                [
                    // NCO
                    'name' => 'officer',
                    'label' => 'Officer',
                    'created_at' => $date,
                    'updated_at' => $date,
                ],
                [
                    // Moderators (CPL)
                    'name' => 'jr_ldr',
                    'label' => 'Junior Leader',
                    'created_at' => $date,
                    'updated_at' => $date,
                ],
                [
                    // SGT
                    'name' => 'sr_ldr',
                    'label' => 'Senior Leader',
                    'created_at' => $date,
                    'updated_at' => $date,
                ],
                [
                    // MSGT+
                    'name' => 'admin',
                    'label' => 'Administrator',
                    'created_at' => $date,
                    'updated_at' => $date,
                ],
            ]
        );
    }
}
