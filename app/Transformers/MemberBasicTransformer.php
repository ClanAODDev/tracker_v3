<?php

namespace App\Transformers;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class MemberBasicTransformer extends Transformer
{
    use HasFactory;

    public function transform($item): array
    {
        return [
            'name'     => $item->name,
            'position' => $item->position->getLabel(),
            'rank'     => [
                'label' => $item->rank->getAbbreviation(),
                'color' => $item->rank->getColorHex(),
            ],
            'discord_id' => (string) $item->discord_id,
        ];
    }
}
