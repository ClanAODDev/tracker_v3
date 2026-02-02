<?php

namespace Database\Seeders;

use App\Models\Division;
use App\Models\DivisionApplicationField;
use Illuminate\Database\Seeder;

class DivisionApplicationFieldSeeder extends Seeder
{
    public function run(): void
    {
        Division::active()->each(function (Division $division) {
            if ($division->applicationFields()->exists()) {
                return;
            }

            $division->applicationFields()->createMany(DivisionApplicationField::DEFAULTS);
        });
    }
}
