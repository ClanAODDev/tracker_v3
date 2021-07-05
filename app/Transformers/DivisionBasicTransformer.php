<?php

namespace App\Transformers;

class DivisionBasicTransformer extends Transformer
{
    public function transform($item, $withMemberData = false): array
    {
        return [
            'name' => $item->name,
            'abbreviation' => $item->abbreviation,
            'description' => $item->description,
        ];
    }
}
