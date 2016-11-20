<?php

use Illuminate\Database\Seeder;

class PositionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('positions')->insert(
            [
                ['name' => 'Member', 'icon' => '', 'class' => 'text-default', 'order' => 0],
                ['name' => 'Squad Leader', 'icon' => 'fa fa-shield', 'class' => 'text-info', 'order' => 0],
                ['name' => 'Platoon Leader', 'icon' => 'fa fa-circle', 'class' => 'text-warning', 'order' => 0],
                ['name' => 'General Sergeant', 'icon' => 'fa', 'class' => 'text-default', 'order' => 0],
                ['name' => 'Executive Officer', 'icon' => 'fa fa-dot-circle-o', 'class' => 'text-danger', 'order' => 0],
                ['name' => 'Commanding Officer', 'icon' => 'fa fa-circle', 'class' => 'text-danger', 'order' => 0],
                ['name' => 'Clan Admin', 'icon' => 'fa fa-square', 'class' => 'text-danger', 'order' => 0],
            ]
        );
    }
}
