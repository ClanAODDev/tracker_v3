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
     * @param $key
     * @return mixed
     *
     * @throws Exception
     */
    public function __get($key)
    {
        if ($this->has($key)) {
            return $this->get($key);
        }

        return false;

        throw new Exception("The {$key} setting does not exist");
    }

    /**
     * Persist a setting.
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
     * Get a setting.
     *
     * @param $key
     * @return mixed
     */
    public function get($key)
    {
        if ($this->has($key)) {
            return Arr::get($this->settings, $key);
        }

        return false;

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
     * @param $key
     * @return bool
     */
    private function has($key)
    {
        return \array_key_exists($key, $this->settings);
    }
}
