<?php

namespace App\Helpers;

/**
 * Class Helpers
 *
 * @package App
 */
class Helpers
{
    public static function avatar($email, $type = "thumb")
    {
        $forum_img = self::GetGravatarUrl($email);
        $unknown = "assets/images/blank_avatar.jpg";
        return "<img src='{$forum_img}' class='img-thumbnail' />";
    }

    private static function GetGravatarUrl($email, $size = 80, $type = 'retro', $rating = 'pg')
    {
        $gravatar = sprintf('http://www.gravatar.com/avatar/%s?d=%s&s=%d&r=%s',
            md5($email), $type, $size, $rating);
        return $gravatar;
    }
}
