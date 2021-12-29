<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class PositionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        \DB::table('positions')->insert(
            [
                ['name' => 'Member', 'icon' => '', 'class' => 'text-default', 'order' => 0],
                ['name' => 'Squad Leader', 'icon' => 'fas fa-shield-alt', 'class' => 'text-info', 'order' => 0],
                ['name' => 'Platoon Leader', 'icon' => 'fas fa-dot-circle', 'class' => 'text-warning', 'order' => 0],
                ['name' => 'General Sergeant', 'icon' => 'fa', 'class' => 'text-default', 'order' => 0],
                ['name' => 'Executive Officer', 'icon' => 'fas fa-circle-notch', 'class' => 'text-danger', 'order' => 0],
                ['name' => 'Commanding Officer', 'icon' => 'fas fa-circle', 'class' => 'text-danger', 'order' => 0],
                ['name' => 'Clan Admin', 'icon' => 'fas fa-square', 'class' => 'text-danger', 'order' => 0],
            ]
        );
    }
}
