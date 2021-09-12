<?php

use App\Models\MemberRequest;
use App\Settings\UserSettings;
use Illuminate\Foundation\Application;
use Illuminate\Support\Arr;

function bytesToHuman($bytes)
{
    $units = ['B', 'KiB', 'MiB', 'GiB', 'TiB', 'PiB'];

    for ($i = 0; $bytes > 1024; ++$i) {
        $bytes /= 1024;
    }

    return round($bytes, 2) . ' ' . $units[$i];
}

function sanitize_filter_attribute($attribute)
{
    // replace dots
    $attribute = str_replace('.', ' ', $attribute);

    // remove pivot references
    $attribute = str_replace('member', '', $attribute);

    // capitalize
    return ucwords($attribute);
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
    $str = "{$n}";
    $t = $n > 9 ? substr($str, -2, 1) : 0;
    $u = substr($str, -1);
    if (1 === $t) {
        return $str . 'th';
    }

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

/**
 * Perform an AOD forum function (pm or email).
 *
 * @param $action  (email, showThread, forumProfile, pm, createThread, replyToThread)
 *
 * @return mixed
 */
function doForumFunction(array $ids, $action)
{
    switch ($action) {
        case 'email':
            $path = 'https://www.clanaod.net/forums/sendmessage.php?';
            $params = ['do' => 'mailmember', 'u' => Arr::first($ids)];

            break;

        case 'showThread':
            $path = 'https://www.clanaod.net/forums/showthread.php?';
            $params = ['t' => Arr::first($ids)];

            break;

        case 'forumProfile':
            $path = 'https://www.clanaod.net/forums/member.php?';
            $params = ['u' => Arr::first($ids)];

            break;

        case 'pm':
            $params = ['do' => 'newpm', 'u' => $ids];
            $path = 'https://www.clanaod.net/forums/private.php?';

            break;

        case 'createThread':
            $params = ['do' => 'newthread', 'f' => Arr::first($ids)];
            $path = 'https://www.clanaod.net/forums/newthread.php?';

            break;

        case 'replyToThread':
            $params = ['do' => 'postreply', 't' => Arr::first($ids)];
            $path = 'https://www.clanaod.net/forums/newreply.php?';

            break;

        default:
            throw new InvalidArgumentException('Improper forum function used: ' . $action);
    }

    return urldecode($path . http_build_query($params));
}

/**
 * Get user settings.
 *
 * @param null $key
 *
 * @return Application|mixed
 */
function UserSettings($key = null)
{
    $settings = app(UserSettings::class);

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

    return asset('/images/logo_v2.svg');
}

/**
 * array_keys with recursive implementation.
 *
 * @param $myArray
 * @param $MAXDEPTH
 * @param int   $depth
 * @param array $arrayKeys
 *
 * @return array
 */
function array_keys_recursive($myArray, $MAXDEPTH = INF, $depth = 0, $arrayKeys = [])
{
    if ($depth < $MAXDEPTH) {
        ++$depth;
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
 * Provides a 'selected' property for dropdown forms.
 *
 * @param $arg1
 * @param $arg2
 *
 * @return string
 */
function selected($arg1, $arg2)
{
    if ($arg1 === $arg2) {
        return 'selected';
    }
}

function checked($arg)
{
    if ($arg) {
        return 'checked';
    }
}

function carbon_date_or_null_if_zero($value)
{
    return (null === $value || Carbon::parse($value)->timestamp <= 0) ? null : $value;
}

/**
 * Provides visual feedback for a member's last activity
 * based on division activity threshold.
 *
 * @param $date
 * @param $division
 *
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
 * Helper for assigning leadership of platoons, squads.
 *
 * @param Eloquent|Model $model
 */
function setLeaderOf(Model $model, Member $member)
{
    $model->leader()->associate($member)->save();

    // Tease out the class name (platoon or squad)
    $modelName = strtolower(getNameOfClass($model));

    // assign the pertinent role (platoon, squad leader)
    $member->assignPosition("{
    {$modelName}} leader")->save();
}

function getNameOfClass($class)
{
    $path = explode('\\', get_class($class));

    return array_pop($path);
}

/**
 * Navigation helper for active classs.
 *
 * @param $path
 * @param string $active
 *
 * @return string
 */
function set_active($path, $active = 'active')
{
    return call_user_func_array('Request::is', (array) $path) ? $active : '';
}

function percent($old_member_count, $new_member_count)
{
    if (0 === $old_member_count || 0 === $new_member_count) {
        return 0;
    }

    return number_format(($old_member_count / $new_member_count) * 100, 2); // yields 0.76
}

/**
 * @return string
 */
function approveMemberPath(MemberRequest $memberRequest)
{
    $base = 'http://www.clanaod.net/forums/modcp/aodmember.php?do=addaod&';

    $args = [
        'userid'   => $memberRequest->member->clan_id,
        'division' => $memberRequest->division->name,
        'rank'     => $memberRequest->member->rank->name,
        // left empty and at the end so it can be adjusted
        'aodname' => '',
    ];

    return $base . http_build_query($args);
}

function gcd($a, $b)
{
    $_a = abs($a);
    $_b = abs($b);

    while (0 !== $_b) {
        $remainder = $_a % $_b;
        $_a = $_b;
        $_b = $remainder;
    }

    return $a;
}

function ratio()
{
    $inputs = func_get_args();
    $c = func_num_args();
    if ($c < 1) {
        return '';
    }
    if (1 === $c) {
        return $inputs[0];
    }
    $gcd = gcd($inputs[0], $inputs[1]);
    for ($i = 2; $i < $c; ++$i) {
        $gcd = gcd($gcd, $inputs[$i]);
    }

    $var = max($inputs[0], 1) / max($gcd, 1);
    for ($i = 1; $i < $c; ++$i) {
        $var .= ':' . round(($inputs[$i] / max($gcd, 1)));
    }

    return $var;
}

/**
 * URL before:
 * https://example.com/orders/123?order=ABC009&status=shipped.
 *
 * 1. remove_query_params(['status'])
 * 2. remove_query_params(['status', 'order'])
 *
 * URL after:
 * 1. https://example.com/orders/123?order=ABC009
 * 2. https://example.com/orders/123
 */
function remove_query_params(array $params = [])
{
    $url = url()->current();
    $query = request()->query();

    foreach ($params as $param) {
        if (str_contains($param, 'filter')) {
            preg_match('/filter\[(.*)\]/', $param, $matches);
            unset($query['filter'][$matches[1]]);
        } else {
            unset($query[$param]);
        }
    }

    return $query ? $url . '?' . http_build_query($query) : $url;
}

/**
 * URL before:
 * https://example.com/orders/123?order=ABC009.
 *
 * 1. add_query_params(['status' => 'shipped'])
 * 2. add_query_params(['status' => 'shipped', 'coupon' => 'CCC2019'])
 *
 * URL after:
 * 1. https://example.com/orders/123?order=ABC009&status=shipped
 * 2. https://example.com/orders/123?order=ABC009&status=shipped&coupon=CCC2019
 */
function add_query_params(array $params = [])
{
    $query = array_merge(
        request()->query(),
        $params
    );

    return url()->current() . '?' . urldecode(http_build_query($query));
}
