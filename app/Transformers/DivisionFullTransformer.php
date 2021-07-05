<?php

namespace App\Transformers;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class DivisionFullTransformer extends Transformer
{
    use HasFactory;

    public function transform($item): array
    {
        return [
            'name' => $item->name,
            'abbreviation' => $item->abbreviation,
            'description' => $item->description,
            'members' => $item->members,
            'sergeants' => $item->sergeants_count,
            'created_at' => $item->created_at,
            'active' => ($item->active),
        ];
    }
}
