<?php

namespace App\Transformers;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class MemberFullTransformer extends Transformer
{
    use HasFactory;

    public function transform($item): array
    {
        return array_merge([
            'name' => $item->name,
            'join_date' => $item->join_date,
            'last_activity' => $item->last_activity,
            'last_ts_activity' => $item->last_ts_activity,
            'rank_id' => $item->rank_id,
            'division_id' => $item->division_id,

        ], count($item->handles) ? [
            'handles' => $item->handles,
        ] : []);
    }
}
