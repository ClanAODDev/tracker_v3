<?php

namespace App\Presenters;

use Exception;

abstract class Presenter
{
    public function __get($property)
    {
        if (method_exists($this, $property)) {
            return \call_user_func([$this, $property]);
        }

        $message = '%s does not respond to the "%s" property or method.';

        throw new Exception(sprintf($message, static::class, $property));
    }
}
