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
            $path = "http://www.clanaod.net/forums/sendmessage.php?";
            $params = ['do' => 'mailmember', 'u' => array_first($clan_id)];
        } else {
            if ($action === "pm") {
                $params = ['do' => 'newpm', 'u' => $clan_id];
                $path = "http://www.clanaod.net/forums/private.php?";
            } else {
                throw new InvalidArgumentException('Invalid action type specified.');
            }
        }
        
        return urldecode($path . http_build_query($params));
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
