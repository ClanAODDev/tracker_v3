<?php

use App\Enums\Rank;

return [
    'logo' => env('LOGO', 'images/logo_v2.png'),

    'discord_webhook' => env('DISCORD_WEBHOOK'),
    'token' => env('AOD_TOKEN'),
    'discord_bot_token' => env('AOD_BOT_TOKEN'),
    'bot_api_base_url' => env('BOT_API_BASE_URL'),
    'bot_cmd_tokens' => env('BOT_COMMAND_TOKENS'),
    'maximum_days_inactive' => env('MAX_DAYS_INACTIVE', 90),

    'request_grace_period' => env('REQUEST_GRACE_PERIOD', 2),
    'stream_calendar' => env('STREAM_CALENDAR_ID'),
    'admin-ticketing-channel' => env('ADMIN_TICKETING_CHANNEL', 'aod-admins'),
    'msgt-channel' => env('MSGT_CHANNEL', 'aod-msgt-up'),

    'rank' => [
        'promotion_acceptance_mins' => 1440,
        'rank_action_min_days' => 7,
        'update_forums' => true,
        'max_squad_leader' => Rank::SPECIALIST,
        'max_platoon_leader' => Rank::CORPORAL,
        'max_division_leader' => Rank::STAFF_SERGEANT,
    ],

    'awards' => [
        'cache_minutes' => env('AWARDS_CACHE_MINUTES', 60),
        'rarity' => [
            'unclaimed' => [
                'min' => 0,
                'max' => 0,
                'label' => 'Unclaimed',
            ],
            'mythic' => [
                'min' => 1,
                'max' => 5,
                'label' => 'Mythic',
            ],
            'legendary' => [
                'min' => 6,
                'max' => 15,
                'label' => 'Legendary',
            ],
            'epic' => [
                'min' => 16,
                'max' => 35,
                'label' => 'Epic',
            ],
            'rare' => [
                'min' => 36,
                'max' => 60,
                'label' => 'Rare',
            ],
            'common' => [
                'min' => 61,
                'max' => null,
                'label' => 'Common',
            ],
        ],
    ],
];
