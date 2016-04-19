<?php

namespace App\Helpers;

/**
 * Class Helpers
 *
 * @package App
 */
class Helpers
{

    /**
     * Returns proper URL for sending an AOD forum PM
     *
     * @param $clan_id
     * @return string
     */
    public static function getAODPmUrl($clan_id)
    {
        $params = [
            'do' => 'newpm',
            'u' => $clan_id,
        ];

        return "http://www.clanaod.net/forums/private.php?" . http_build_query($params);
    }

    /**
     * Returns proper URL for sending an AOD forum email
     *
     * @param $clan_id
     * @return string
     */
    public static function getAODEmailUrl($clan_id)
    {
        $params = [
            'do' => 'mailmember',
            'u' => $clan_id,
        ];

        return "http://www.clanaod.net/forums/sendmessage.php?" . http_build_query($params);
    }

    /**
     * Return gravatar image
     *
     * @param $email
     * @param string $type
     * @return string
     */
    public static function avatar($email, $type = "thumb")
    {
        $forum_img = self::GetGravatarUrl($email);
        $unknown = "assets/images/blank_avatar.jpg";

        return "<img src='{$forum_img}' class='img-thumbnail' />";
    }

    /**
     * Generate a gravatar URL
     *
     * @param $email
     * @param int $size
     * @param string $type
     * @param string $rating
     * @return mixed
     */
    private static function GetGravatarUrl($email, $size = 80, $type = 'retro', $rating = 'pg')
    {
        $gravatar = sprintf('http://www.gravatar.com/avatar/%s?d=%s&s=%d&r=%s',
            md5($email), $type, $size, $rating);

        return $gravatar;
    }
}
