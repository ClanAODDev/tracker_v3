<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private array $fallenMembers = [
        ['name' => 'Kevin "Bluntz" Lovelace', 'date_fallen' => '2021-11-14', 'forum_profile' => 'https://www.clanaod.net/forums/member.php?u=3419'],
        ['name' => 'AchesAndPains', 'date_fallen' => '2020-07-17', 'forum_profile' => 'https://www.clanaod.net/forums/member.php?u=33030'],
        ['name' => 'Bruce "hailhydra2018" Kennedy', 'date_fallen' => '2020-01-18', 'forum_profile' => 'https://www.clanaod.net/forums/member.php?u=62669'],
        ['name' => 'MD9445', 'date_fallen' => null, 'forum_profile' => 'https://www.clanaod.net/forums/member.php?u=20244'],
        ['name' => 'Quake-id', 'date_fallen' => null, 'forum_profile' => 'https://www.clanaod.net/forums/member.php?u=56057'],
        ['name' => 'Lance "Syph3n" Groth', 'date_fallen' => '2015-01-12', 'forum_profile' => 'https://www.clanaod.net/forums/member.php?u=26433'],
        ['name' => 'oc675', 'date_fallen' => '2015-11-08', 'forum_profile' => 'https://www.clanaod.net/forums/member.php?u=35067'],
        ['name' => 'Pafire', 'date_fallen' => '2020-11-05', 'forum_profile' => 'https://www.clanaod.net/forums/member.php?u=51680'],
        ['name' => 'William "T Dooly" McCoy', 'date_fallen' => '2023-07-22', 'forum_profile' => 'https://www.clanaod.net/forums/member.php?u=49952'],
        ['name' => 'Stefan "Drakooth" Erfmann', 'date_fallen' => '2024-03-04', 'forum_profile' => 'https://www.clanaod.net/forums/member.php?u=85302'],
    ];

    public function up(): void
    {
        $now = now();

        foreach ($this->fallenMembers as $index => $fallenMember) {
            DB::table('fallen_members')->insert([
                ...$fallenMember,
                'display_order' => ($index + 1) * 10,
                'created_at'    => $now,
                'updated_at'    => $now,
            ]);
        }
    }

    public function down(): void
    {
        DB::table('fallen_members')
            ->whereIn('forum_profile', array_column($this->fallenMembers, 'forum_profile'))
            ->delete();
    }
};
