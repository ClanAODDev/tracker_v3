<?php

/**
 * Perform an AOD forum function (pm or email)
 *
 * @param array $clan_id
 * @param $action
 * @return mixed
 */
function doForumFunction(array $clan_id, $action)
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
function avatar($email, $type = "thumb")
{
    $forum_img = GetGravatarUrl($email);
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
function GetGravatarUrl($email, $size = 80, $type = 'retro', $rating = 'pg')
{
    $gravatar = sprintf('http://www.gravatar.com/avatar/%s?d=%s&s=%d&r=%s',
        md5($email), $type, $size, $rating);

    return $gravatar;
}

/**
 * Get user settings
 *
 * @param null $key
 * @return \Illuminate\Foundation\Application|mixed
 */
function UserSettings($key = null)
{
    $settings = app('App\Settings\UserSettings');

    return $key ? $settings->get($key) : $settings;
}

/**
 * array_keys with recursive implementation
 *
 * @param $myArray
 * @param $MAXDEPTH
 * @param int $depth
 * @param array $arrayKeys
 * @return array
 */
function array_keys_recursive($myArray, $MAXDEPTH = INF, $depth = 0, $arrayKeys = array()){
    if($depth < $MAXDEPTH){
        $depth++;
        $keys = array_keys($myArray);
        foreach($keys as $key){
            if(is_array($myArray[$key])){
                $arrayKeys[$key] = array_keys_recursive($myArray[$key], $MAXDEPTH, $depth);
            }
        }
    }

    return $arrayKeys;
}