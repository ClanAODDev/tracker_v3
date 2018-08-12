<?php

function bytesToHuman($bytes)
{
    $units = ['B', 'KiB', 'MiB', 'GiB', 'TiB', 'PiB'];

    for ($i = 0; $bytes > 1024; $i++) {
        $bytes /= 1024;
    }

    return round($bytes, 2) . ' ' . $units[$i];
}

function bungieMemberType($type)
{
    switch ($type) {
        case 5:
            return 'Owner';
        case 3:
            return 'Admin';
        case 2:
            return 'Member';
        case 1:
            return 'Prospective';
    }
}

function ordSuffix($n)
{
    $str = "$n";
    $t = $n > 9 ? substr($str, -2, 1) : 0;
    $u = substr($str, -1);
    if ($t == 1) {
        return $str . 'th';
    } else {
        switch ($u) {
            case 1:
                return $str . 'st';
            case 2:
                return $str . 'nd';
            case 3:
                return $str . 'rd';
            default:
                return $str . 'th';
        }
    }
}


/**
 * Perform an AOD forum function (pm or email)
 *
 * @param array $ids
 * @param $action
 * @return mixed
 */
function doForumFunction(array $ids, $action)
{

    switch ($action) {
        case "email":
            $path = "https://www.clanaod.net/forums/sendmessage.php?";
            $params = ['do' => 'mailmember', 'u' => array_first($ids)];
            break;
        case "showThread":
            $path = "https://www.clanaod.net/forums/showthread.php?";
            $params = ['t' => array_first($ids)];
            break;
        case "forumProfile":
            $path = "https://www.clanaod.net/forums/member.php?";
            $params = ['u' => array_first($ids)];
            break;
        case "pm":
            $params = ['do' => 'newpm', 'u' => $ids];
            $path = "https://www.clanaod.net/forums/private.php?";
            break;
        case "createThread":
            $params = ['do' => 'newthread', 'f' => array_first($ids)];
            $path = "https://www.clanaod.net/forums/newthread.php?";
            break;
        case "replyToThread":
            $params = ['do' => 'postreply', 't' => array_first($ids)];
            $path = "https://www.clanaod.net/forums/newreply.php?";
            break;
        default:
            throw new InvalidArgumentException('Improper forum function used: ' . $action);
    }

    return urldecode($path . http_build_query($params));
}

/**
 * Get user settings
 *
 * @param null $key
 * @return \Illuminate\Foundation\Application|mixed
 */
function UserSettings($key = null)
{
    $settings = app(\App\Settings\UserSettings::class);

    return $key ? $settings->get($key) : $settings;
}

function hasDivisionIcon($abbreviation)
{
    $image = public_path() . "/images/game_icons/48x48/{$abbreviation}.png";

    return File::exists($image);
}

function getDivisionIconPath($abbreviation)
{
    if (hasDivisionIcon($abbreviation)) {
        return asset("/images/game_icons/48x48/{$abbreviation}.png");
    }

    return asset("/images/logo_v2.svg");
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
function array_keys_recursive($myArray, $MAXDEPTH = INF, $depth = 0, $arrayKeys = [])
{
    if ($depth < $MAXDEPTH) {
        $depth++;
        $keys = array_keys($myArray);
        foreach ($keys as $key) {
            if (is_array($myArray[$key])) {
                $arrayKeys[$key] = array_keys_recursive($myArray[$key], $MAXDEPTH, $depth);
            }
        }
    }

    return $arrayKeys;
}

/**
 * Provides a 'selected' property for dropdown forms
 *
 * @param $arg1
 * @param $arg2
 * @return string
 */
function selected($arg1, $arg2)
{
    if ($arg1 == $arg2) {
        return "selected";
    }
}

function checked($arg)
{
    if ($arg) {
        return "checked";
    }
}

function carbon_date_or_null_if_zero($value)
{
    return (is_null($value) || Carbon::parse($value)->timestamp <= 0) ? null : $value;
}

/**
 * Provides visual feedback for a member's last activity
 * based on division activity threshold
 *
 * @param $date
 * @param $division
 * @return string
 */
function getActivityClass($date, $division)
{
    $limits = $division->settings()
        ->get('activity_threshold');

    $days = $date->diffInDays();

    foreach ($limits as $limit) {
        if ($days >= $limit['days']) {
            return $limit['class'];
        }
    }

    return 'text-success';
}

/**
 * Helper for assigning leadership of platoons, squads
 *
 * @param Member $member
 * @param Eloquent|Model $model
 */
function setLeaderOf(Model $model, Member $member)
{
    $model->leader()->associate($member)->save();

    // Tease out the class name (platoon or squad)
    $modelName = strtolower(getNameOfClass($model));

    // assign the pertinent role (platoon, squad leader)
    $member->assignPosition("{
    $modelName} leader")->save();
}

function getNameOfClass($class)
{
    $path = explode('\\', get_class($class));

    return array_pop($path);
}

/**
 * Navigation helper for active classs
 *
 * @param $path
 * @param string $active
 * @return string
 */
function set_active($path, $active = 'active')
{
    return call_user_func_array('Request::is', (array) $path) ? $active : '';
}

function percent($old_member_count, $new_member_count)
{
    if ($old_member_count == 0 || $new_member_count == 0) {
        return 0;
    }

    return number_format(($old_member_count / $new_member_count) * 100, 2); // yields 0.76
}

function curl_last_url($ch, &$maxredirect = null)
{
    $mr = $maxredirect === null ? 3 : intval($maxredirect);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
    if ($mr > 0) {
        $newurl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
        $rch = curl_copy_handle($ch);
        curl_setopt($rch, CURLOPT_HEADER, true);
        curl_setopt($rch, CURLOPT_NOBODY, true);
        curl_setopt($rch, CURLOPT_FORBID_REUSE, false);
        curl_setopt($rch, CURLOPT_RETURNTRANSFER, true);
        do {
            curl_setopt($rch, CURLOPT_URL, $newurl);
            $header = curl_exec($rch);
            if (curl_errno($rch)) {
                $code = 0;
            } else {
                $code = curl_getinfo($rch, CURLINFO_HTTP_CODE);
                // echo $code;
                if ($code == 301 || $code == 302) {
                    preg_match('/Location:(.*?)\n/', $header, $matches);
                    $newurl = trim(array_pop($matches));
                } else {
                    $code = 0;
                }
            }
        } while ($code && --$mr);
        curl_close($rch);
        if (! $mr) {
            if ($maxredirect === null) {
                trigger_error(
                    'Too many redirects. When following redirects, libcurl hit the maximum amount.',
                    E_USER_WARNING
                );
            } else {
                $maxredirect = 0;
            }

            return false;
        }
        curl_setopt($ch, CURLOPT_URL, $newurl);
    }

    return $newurl;
}
