<?php

namespace Database\Seeders;

use App\Models\Division;
use App\Models\DivisionApplication;
use App\Models\User;
use Illuminate\Database\Seeder;

class DivisionApplicationSeeder extends Seeder
{
    private array $pendingDiscordUsers = [
        ['name' => 'ghostrider_99', 'discord_id' => '100000000000000001', 'discord_username' => 'ghostrider_99'],
        ['name' => 'neon_viper', 'discord_id' => '100000000000000002', 'discord_username' => 'neon_viper'],
        ['name' => 'pixel_storm', 'discord_id' => '100000000000000003', 'discord_username' => 'pixel_storm'],
        ['name' => 'frost_byte42', 'discord_id' => '100000000000000004', 'discord_username' => 'frost_byte42'],
        ['name' => 'shadow_lynx', 'discord_id' => '100000000000000005', 'discord_username' => 'shadow_lynx'],
        ['name' => 'turbo_gecko', 'discord_id' => '100000000000000006', 'discord_username' => 'turbo_gecko'],
        ['name' => 'cosmic_drift', 'discord_id' => '100000000000000007', 'discord_username' => 'cosmic_drift'],
    ];

    private array $customFields = [
        [
            'type' => 'text',
            'label' => 'What region do you play in?',
            'helper_text' => 'NA East, NA West, EU, etc.',
            'required' => true,
            'display_order' => 1,
        ],
        [
            'type' => 'textarea',
            'label' => 'Tell us about your experience with the game',
            'required' => true,
            'display_order' => 2,
        ],
        [
            'type' => 'text',
            'label' => 'How did you hear about AOD?',
            'required' => true,
            'display_order' => 3,
        ],
        [
            'type' => 'textarea',
            'label' => 'What are you looking for in a gaming community?',
            'required' => true,
            'display_order' => 4,
        ],
        [
            'type' => 'radio',
            'label' => 'Are you 18 or older?',
            'required' => true,
            'display_order' => 5,
            'options' => [
                ['label' => 'Yes'],
                ['label' => 'No'],
            ],
        ],
    ];

    private array $defaultResponses = [
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

    private array $customResponses = [
        [
            'text' => ['NA East', 'Reddit LFG post'],
            'textarea' => [
                'Been playing since early access, mostly ranked. Hit Diamond last season. I main support but can flex to DPS when needed.',
                'Looking for a chill group to play with regularly. Tired of solo queue and random toxicity. Want people who communicate and have fun.',
            ],
            'radio' => 'Yes',
        ],
        [
            'text' => ['EU West', 'Friend recommended AOD'],
            'textarea' => [
                'Fairly new, picked it up about two months ago. Still learning the meta but I watch a lot of guides and I improve fast.',
                "I want a community that actually plays together and doesn't just exist on paper. Regular events and people to group up with would be great.",
            ],
            'radio' => 'Yes',
        ],
        [
            'text' => ['NA West', 'Saw an AOD member in-game'],
            'textarea' => [
                'Played the original and jumped into this one at launch. I mostly do PvE content but getting into PvP more. Have about 300 hours total.',
                "Something organized but not sweaty. I have a full-time job so I can't commit to mandatory raid schedules, but I'm on most evenings.",
            ],
            'radio' => 'Yes',
        ],
        [
            'text' => ['NA East', 'Google search for gaming clans'],
            'textarea' => [
                'Competitive background in other FPS games. Was Global Elite in CS and Immortal in Valorant. Switched to this game recently for something fresh.',
                "I'm looking for people who take the game seriously enough to improve but aren't toxic about it. Good vibes and teamwork are more important than rank to me.",
            ],
            'radio' => 'Yes',
        ],
    ];

    public function run(): void
    {
        $this->createPendingDiscordUsers();
        $this->seedSecondDivision();
        $this->seedDefaultDivisions();
    }

    private function createPendingDiscordUsers(): void
    {
        foreach ($this->pendingDiscordUsers as $userData) {
            User::firstOrCreate(
                ['discord_id' => $userData['discord_id']],
                [
                    'name' => $userData['name'],
                    'email' => $userData['name'] . '@example.com',
                    'discord_username' => $userData['discord_username'],
                ],
            );
        }
    }

    private function seedSecondDivision(): void
    {
        $division = Division::active()
            ->where('slug', '!=', 'battlefield')
            ->whereHas('applicationFields')
            ->first();

        if (! $division) {
            return;
        }

        $division->applicationFields()->delete();
        $division->applicationFields()->createMany($this->customFields);

        $settings = $division->settings ?? [];
        $settings['application_required'] = true;
        $division->settings = $settings;
        $division->save();

        $this->seedApplications($division, $this->customResponses, 4);
    }

    private function seedDefaultDivisions(): void
    {
        $division = Division::active()
            ->whereHas('applicationFields')
            ->whereDoesntHave('applications', fn ($q) => $q->whereNull('recruited_at'))
            ->first();

        if (! $division) {
            return;
        }

        $settings = $division->settings ?? [];
        $settings['application_required'] = true;
        $division->settings = $settings;
        $division->save();

        $this->seedApplications($division, $this->defaultResponses, 3);
    }

    private function seedApplications(Division $division, array $sampleResponses, int $count): void
    {
        $fields = $division->applicationFields()->orderBy('display_order')->get();
        $pendingUsers = User::pendingDiscord()
            ->whereDoesntHave('divisionApplication')
            ->take($count)
            ->get();

        foreach ($pendingUsers as $index => $user) {
            $sample = $sampleResponses[$index % count($sampleResponses)];
            $responses = [];
            $typeCounters = [];

            foreach ($fields as $field) {
                $typeCounters[$field->type] = ($typeCounters[$field->type] ?? -1) + 1;
                $value = is_array($sample[$field->type] ?? null)
                    ? ($sample[$field->type][$typeCounters[$field->type]] ?? '')
                    : ($sample[$field->type] ?? '');

                $responses[$field->id] = [
                    'label' => $field->label,
                    'value' => $value,
                ];
            }

            DivisionApplication::create([
                'user_id' => $user->id,
                'division_id' => $division->id,
                'responses' => $responses,
            ]);
        }
    }
}
