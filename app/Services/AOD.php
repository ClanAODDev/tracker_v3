<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;

class AOD extends Model
{
    const AGENT = 'AOD Division Tracker';

    /**
     * @param $url
     * @param array $options
     * @return bool|string
     */
    public static function request($url, $options = [])
    {
        $endpoint = (new AOD)->prepareRequestUrl($url, $options);

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_USERAGENT, self::AGENT);
        curl_setopt($ch, CURLOPT_URL, $endpoint);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

        $data = curl_exec($ch);
        curl_close($ch);

        return json_decode(utf8_encode($data)) ?? $data;
    }

    /**
     * Builds a proper request URL
     *
     * @param $url
     * @param array $options
     * @return string
     */
    private function prepareRequestUrl($url, array $options)
    {
        $arguments = array_merge($options, [
            'authcode2' => $this->generateToken(),
        ]);

        return $url . http_build_query($arguments);
    }

    /**
     * Generates authentication token for AOD API
     *
     * @return string
     */
    private function generateToken()
    {
        $currentMinute = floor(time() / 60) * 60;

        return md5($currentMinute . config('app.aod.token'));
    }
}
