<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DivisionApplicationField extends Model
{
    protected $guarded = [];

    protected $casts = [
        'options' => 'array',
        'required' => 'boolean',
        'display_order' => 'integer',
    ];

    public const array DEFAULTS = [
        [
            'type' => 'text',
            'label' => 'What is your timezone & location?',
            'helper_text' => 'AOD is generally EST based',
            'required' => true,
            'display_order' => 3,
        ],
        [
            'type' => 'textarea',
            'label' => 'Are you currently in any other gaming communities/clans/guilds? (if yes, which one(s) and for which games)',
            'required' => true,
            'display_order' => 3,
        ],
        [
            'type' => 'text',
            'label' => 'What is your ingame name?',
            'required' => true,
            'display_order' => 4,
        ],
        [
            'type' => 'radio',
            'label' => 'AOD is an honor clan. We prefer good sportsmanship over skill. Can you be happy in an environment that doesn\'t reward skill?',
            'required' => true,
            'display_order' => 5,
            'options' => [
                ['label' => 'Yes'],
                ['label' => 'No'],
            ],
        ],
    ];

    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class);
    }
}
