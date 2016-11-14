<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRanksTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ranks', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('abbreviation');
            $table->timestamps();
        });

        /**
         * populate with aod rank data
         */
        DB::table('ranks')->insert(
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

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('ranks');
    }
}
