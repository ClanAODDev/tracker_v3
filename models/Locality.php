<?php

class Locality extends Application
{
    public $id;
    public $subject;
    public $replace;
    public $game_id;

    public static $id_field = 'id';
    public static $table = 'locality';

    /**
     * localization functionality
     * @param  string $string the subject of the localization
     * @param  int $game      the gaming division. (division: id)
     * @return string         the converted string, or if no match is found, the original string
     */
    public static function run($string, $game)
    {
        $params = (object) self::find(array('subject' => strtolower(trim($string)), 'game_id' => $game));
        if (property_exists($params, 'replace')) {
            return ucwords(str_replace($string, $params->replace, $string));
        } else {
            return ucwords($string);
        }
    }
}
