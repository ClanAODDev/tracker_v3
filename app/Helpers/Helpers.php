<?php

namespace App\Helpers;

use Doctrine\Instantiator\Exception\InvalidArgumentException;

/**
 * Class Helpers
 *
 * @package App
 */
class Helpers
{
    /**
     * Perform an AOD forum function (pm or email)
     *
     * @param array $clan_id
     * @param $action
     * @return mixed
     */
    public static function doForumFunction(array $clan_id, $action)
    {
        if ($action === "email") {
            $clan_id = array_first($clan_id);
            $action = "mailmember";
            $path = "http://www.clanaod.net/forums/sendmessage.php?";
        } else if ($action === "pm") {
            $action = "newpm";
            $path = "http://www.clanaod.net/forums/private.php?";
        } else {
            throw new InvalidArgumentException('Invalid action type specified.');
        }

        $params = [
            'do' => $action,
            'u' => $clan_id,
        ];

        $url = $path . http_build_query($params);

        return urldecode($url);
    }

    /**
     * Return gravatar image
     *
     * @param $email
     * @param string $type
     * @return string
     */
    public
    static function avatar($email, $type = "thumb")
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
    private
    static function GetGravatarUrl($email, $size = 80, $type = 'retro', $rating = 'pg')
    {
        $gravatar = sprintf('http://www.gravatar.com/avatar/%s?d=%s&s=%d&r=%s',
            md5($email), $type, $size, $rating);

        return $gravatar;
    }
}
