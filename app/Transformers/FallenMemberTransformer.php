<?php

namespace App\Transformers;

class FallenMemberTransformer extends Transformer
{
    public function transform($item): array
    {
        return [
            'name'          => $item->name,
            'date_fallen'   => $item->date_fallen?->format('m/d/Y') ?? 'Unknown',
            'forum_profile' => $item->forum_profile,
        ];
    }
}
