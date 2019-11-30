<?php

namespace App\Settings;

use Illuminate\Support\Arr;
use Exception;

trait Settable
{
    protected $settings = [];

    /**
     * Persist a setting
     *
     * @param $key
     * @param $value
     */
    public function set($key, $value)
    {
        $this->settings[$key] = $value;

        $this->persist();
    }

    /**
     * Magic getter for settings
     *
     * @param $key
     * @return mixed
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
     * Checks to see if a key exists
     *
     * @param $key
     * @return bool
     */
    private function has($key)
    {
        return array_key_exists($key, $this->settings);
    }

    /**
     * Get a setting
     *
     * @param $key
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
     * Update settings
     *
     * @param array $attributes
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
     * Returns all settings
     *
     * @return mixed
     */
    public function all()
    {
        return $this->settings;
    }
}
