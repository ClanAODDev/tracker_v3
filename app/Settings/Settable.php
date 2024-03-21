<?php

namespace App\Settings;

use Exception;
use Illuminate\Support\Arr;

trait Settable
{
    protected $settings = [];

    /**
     * Magic getter for settings.
     *
     * @return mixed
     *
     * @throws Exception
     */
    public function __get($key)
    {
        if ($this->has($key)) {
            return $this->get($key);
        }

        throw new Exception("The {$key} setting does not exist");
    }

    /**
     * Persist a setting.
     */
    public function set($key, $value)
    {
        $this->settings[$key] = $value;

        $this->persist();
    }

    /**
     * Get a setting.
     *
     * @return mixed
     */
    public function get($key)
    {
        if ($this->has($key)) {
            return Arr::get($this->settings, $key);
        }

        throw new Exception("The {$key} setting does not exist");
    }

    /**
     * Update settings.
     *
     * @return mixed
     */
    public function merge(array $attributes)
    {
        $this->settings = array_merge(
            $this->settings,
            Arr::only($attributes, array_keys($this->settings))
        );

        return $this->persist();
    }

    /**
     * Returns all settings.
     *
     * @return mixed
     */
    public function all()
    {
        return $this->settings;
    }

    /**
     * Checks to see if a key exists.
     *
     * @return bool
     */
    private function has($key)
    {
        return \array_key_exists($key, $this->settings);
    }
}
