<?php

/**
 * Perform an AOD forum function (pm or email)
 *
 * @param array $ids
 * @param $action
 * @return mixed
 */
function doForumFunction(array $ids, $action)
{
    if ($action === "email") {
        $path = "https://www.clanaod.net/forums/sendmessage.php?";
        $params = ['do' => 'mailmember', 'u' => array_first($ids)];
    } else {
        if ($action === 'showThread') {
            $path = "https://www.clanaod.net/forums/showthread.php?";
            $params = ['t' => array_first($ids)];
        } else {
            if ($action === 'forumProfile') {
                $path = "https://www.clanaod.net/forums/member.php?";
                $params = ['u' => array_first($ids)];
            } else {
                if ($action === "pm") {
                    $params = ['do' => 'newpm', 'u' => $ids];
                    $path = "https://www.clanaod.net/forums/private.php?";
                } else {
                    throw new InvalidArgumentException('Invalid action type specified.');
                }
            }
        }
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

    return number_format((1 - $old_member_count / $new_member_count) * 100, 2); // yields 0.76
}

