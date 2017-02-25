<?php

namespace App\Transformers;

class DivisionTransformer extends Transformer
{
    /**
     * @param $division
     * @return array
     */
    public function transform($division)
    {
        return [
            'name' => $division->name,
            'abbreviation' => $division->abbreviation,
            'members' => $division->members()->count()
        ];
    }
}
