<?php

namespace App\Settings;

use App\Models\Division;

class DivisionSettings
{
    use Settable;

    protected $division;

    /**
     * Settings constructor.
     *
     * @param  Division|DivisionSettings  $division
     */
    public function __construct(array $settings, Division $division)
    {
        $this->settings = $settings;
        $this->division = $division;
    }

    protected function persist()
    {
        return $this->division->update(['settings' => $this->settings]);
    }

    public function only(array $keys): array
    {
        return array_intersect_key($this->settings, array_flip($keys));
    }
}
