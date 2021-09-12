<?php

namespace App\Transformers;

/**
 * Class Transformer.
 */
abstract class Transformer
{
    public function transformCollection($items): array
    {
        return array_map([$this, 'transform'], $items);
    }

    abstract public function transform($item): array;
}
