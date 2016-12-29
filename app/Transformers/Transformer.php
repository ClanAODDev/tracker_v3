<?php

namespace App\Transformers;

/**
 * Class Transformer
 * @package App\Transformers
 */
abstract class Transformer
{
    public function transformCollection(array $items)
    {
        return array_map([$this, 'transform'], $items);
    }

    public abstract function transform($item);
}