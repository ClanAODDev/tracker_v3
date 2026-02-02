<?php

namespace Database\Seeders;

use App\Models\Division;
use App\Models\DivisionApplication;
use App\Models\User;
use Illuminate\Database\Seeder;

class DivisionApplicationSeeder extends Seeder
{
    private array $sampleResponses = [
        [
            'text' => 'EST, Virginia',
            'textarea' => 'I was in a small guild in WoW a few years ago but nothing active right now.',
            'radio' => 'Yes',
        ],
        [
            'text' => 'PST, California',
            'textarea' => 'No, this would be my first clan.',
            'radio' => 'Yes',
        ],
        [
            'text' => 'CST, Texas',
            'textarea' => 'I play casually with a group of friends but no formal clan or guild.',
            'radio' => 'Yes',
        ],
    ];

    public function run(): void
    {
        $divisions = Division::active()
            ->whereHas('applicationFields')
            ->get();

        foreach ($divisions as $division) {
            $fields = $division->applicationFields()->orderBy('display_order')->get();
            $pendingUsers = User::pendingDiscord()
                ->whereDoesntHave('divisionApplication')
                ->take(3)
                ->get();

            foreach ($pendingUsers as $index => $user) {
                $sample = $this->sampleResponses[$index % count($this->sampleResponses)];
                $responses = [];

                foreach ($fields as $field) {
                    $responses[$field->id] = $sample[$field->type] ?? '';
                }

                $user->update(['division_id' => $division->id]);

                DivisionApplication::create([
                    'user_id' => $user->id,
                    'division_id' => $division->id,
                    'responses' => $responses,
                ]);
            }
        }
    }
}
