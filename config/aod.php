<?php

return [
    'awards' => [
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
