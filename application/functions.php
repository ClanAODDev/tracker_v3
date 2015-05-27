<?php

require_once 'uagent.php';

/**
 * converts role id into real string
 * @param  int $role role id (aod.members)
 * @return string    the real string, contextual position
 */
function getUserRoleName($role)
{
    switch ($role) {
        case 0:
        $role = "User";
        break;
        case 1:
        $role = "Squad Leader";
        break;
        case 2:
        $role = "Platoon Leader";
        break;
        case 3:
        $role = "Command Staff";
        break;
        case 4:
        $role = "Administrator";
        break;
    }
    return $role;
}

function activityIcon($type) {
    switch ($type) {
        case 1:
        $icon = "user-plus text-success";
        break;
        case 2:
        $icon = "user-times text-danger";
        break;
        case 3:
        $icon = "pencil-square-o text-info";
        break;
        case 4:
        $icon = "flag text-danger";
        break;
        case 5:
        $icon = "cog";
        break;
        case 6:
        $icon = "flag text-warning";
        break;
        case 7:
        $icon = "thumbs-o-up text-success";
        break;
        case 8:
        $icon = "thumbs-o-down text-danger";
        break;
        case 9:
        $icon = "user-times text-danger";
        break;
        case 10:
        $icon = "refresh text-success";
        break;
    }
    return $icon;
}

/**
 * password hash generation
 */
function hasher($info, $encdata = false)
{
    $strength = "10";
    
    //if encrypted data is passed, check it against input ($info) 
    if ($encdata) {
        if (substr($encdata, 0, 60) == crypt($info, "$2a$" . $strength . "$" . substr($encdata, 60))) {
            return true;
            
        } else {
            return false;
        }
    } else {

        //make a salt and hash it with input, and add salt to end 
        $salt = "";
        for ($i = 0; $i < 22; $i++) {
            $salt .= substr("./ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789", mt_rand(0, 63), 1);
        }
        //return 82 char string (60 char hash & 22 char salt) 
        return crypt($info, "$2a$" . $strength . "$" . $salt) . $salt;
        
    }
}

/**
 * generates a human readable number suffix
 * @param  int $n 
 * @return string    
 */
function ordSuffix($n)
{
    $str = "$n";
    $t   = $n > 9 ? substr($str, -2, 1) : 0;
    $u   = substr($str, -1);
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
 * force destruction of session and all cookies (logout)
 * @return null
 */
function forceEndSession()
{
    $_SESSION = array();
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
    }
    
    session_destroy();
}

/**
 * helper function -- array->object
 * @param  array $d 
 * @return object    
 */
function arrayToObject($d) {
    if (is_array($d)) {
        return (object) array_map(__FUNCTION__, $d);
    }
    else {
        return $d;
    }
}

/**
 * helper function -- object->array
 * @param  object $d 
 * @return array
 */
function objectToArray($d) {
    if (is_object($d)) {
        $d = get_object_vars($d);
    }

    if (is_array($d)) {
        return array_map(__FUNCTION__, $d);
    }
    else {

        return $d;
    }
}

/**
 * returns human readable time in past (2 seconds ago, 12 hours ago etc)
 * @param  int $ptime date
 * @return string        
 */
function formatTime($ptime)
{
    $etime = time() - $ptime;
    
    if ($etime < 1) {
        return '0 seconds';
    }
    
    $a        = array(
        365 * 24 * 60 * 60 => 'year',
        30 * 24 * 60 * 60 => 'month',
        24 * 60 * 60 => 'day',
        60 * 60 => 'hour',
        60 => 'minute',
        1 => 'second'
        );
    $a_plural = array(
        'year' => 'years',
        'month' => 'months',
        'day' => 'days',
        'hour' => 'hours',
        'minute' => 'minutes',
        'second' => 'seconds'
        );
    
    foreach ($a as $secs => $str) {
        $d = $etime / $secs;
        if ($d >= 1) {
            $r = round($d);
            return $r . ' ' . ($r > 1 ? $a_plural[$str] : $str) . ' ago';
        }
    }
}

function lastSeenFlag($last_seen)
{
    if (strtotime($last_seen) < strtotime('-30 days')) {
        $status = "<i class='fa fa-flag text-danger'></i>";
    } else if (strtotime($last_seen) < strtotime('-14 days')) {
        $status = "<i class='fa fa-flag text-warning'></i>";
    } else {
        $status = NULL;
    }
    return $status;
}

function lastSeenColored($last_seen)
{
    if (strtotime($last_seen) < strtotime('-30 days')) {
        $status = 'danger';
    } else if (strtotime($last_seen) < strtotime('-14 days')) {
        $status = 'warning';
    } else {
        $status = 'default';
    }
    return $status;
}


/**
 * class name for last_seen column (inactivity)
 * @param  timestamp $last_seen 
 * @return string            
 */
function inactiveClass($last_seen) {
    if (strtotime($last_seen) < strtotime('-30 days')) {
        $status = 'danger';
    } else if (strtotime($last_seen) < strtotime('-14 days')) {
        $status = 'warning';
    } else {
        $status = 'muted';
    }
    return $status;
}

/**
 * convert single digit to word
 */
function singledigitToWord($number)
{
    switch ($number) {
        case 0:
        $word = "zero";
        break;
        case 1:
        $word = "one";
        break;
        case 2:
        $word = "two";
        break;
        case 3:
        $word = "three";
        break;
        case 4:
        $word = "four";
        break;
        case 5:
        $word = "five";
        break;
        case 6:
        $word = "six";
        break;
        case 7:
        $word = "seven";
        break;
        case 8:
        $word = "eight";
        break;
        case 9:
        $word = "nine";
        break;
    }
    return $word;
}


function getPercentageColor($pct)
{
    if ($pct >= PERCENTAGE_CUTOFF_GREEN) {
        $percent_class = "success";
    } else if ($pct >= PERCENTAGE_CUTOFF_AMBER) {
        $percent_class = "warning";
    } else {
        $percent_class = "danger";
    }
    return $percent_class;
}


/**
 * colors for users online list
 * @param  string $user user's name
 * @param  int $level role level
 * @return string combined role string
 */
function userColor($user, $level, $last_seen)
{

    $last_seen = formatTime(strtotime($last_seen));

    switch ($level) {
        case 99:
        $span = "<span class='text-muted tool-user idling' title='Last active: {$last_seen}'>" . $user . "</span>";
        break;
        case 4:
        $span = "<span class='text-danger tool-user' title='Last active: {$last_seen}'>" . $user . "</span>";
        break;
        case 3:
        $span = "<span class='text-warning tool-user' title='Last active: {$last_seen}'>" . $user . "</span>";
        break;
        case 2:
        $span = "<span class='text-info tool-user' title='Last active: {$last_seen}'>" . $user . "</span>";
        break;
        case 1:
        $span = "<span class='text-primary tool-user' title='Last active: {$last_seen}'>" . $user . "</span>";
        break;
        default:
        $span = "<span class='text-muted tool-user' title='Last active: {$last_seen}'>" . $user . "</span>";
        break;
    }
    
    return $span;
}


/**
 * colors for member tables
 * @param  string $user user's name
 * @param  int $level role level
 * @return string combined role string
 */
function memberColor($user, $level)
{

    switch ($level) {
        case 3:
        case 8:
        $span = "<span class='text-danger tool' title='Administrator'><i class='fa fa-shield '></i> " . $user . "</span>";
        break;
        case 2:
        case 1:
        $span = "<span class='text-warning tool' title='Command Staff'><i class='fa fa-shield '></i> " . $user . "</span>";
        break;
        case 4:
        $span = "<span class='text-info tool' title='Platoon Leader'><i class='fa fa-shield '></i> " . $user . "</span>";
        break;
        case 5:
        $span = "<span class='text-primary tool' title='Squad Leader'><i class='fa fa-shield '></i> " . $user . "</span>";
        break;
        default:
        $span = $user;
        break;
    }
    
    return $span;
}


function average($array) {
 return array_sum($array) / count($array);
}


function curl_last_url( /*resource*/ $ch, /*int*/ &$maxredirect = null)
{
    $mr = $maxredirect === null ? 5 : intval($maxredirect);
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
        if (!$mr) {
            if ($maxredirect === null) {
                trigger_error('Too many redirects. When following redirects, libcurl hit the maximum amount.', E_USER_WARNING);
            } else {
                $maxredirect = 0;
            }
            return false;
        }
        curl_setopt($ch, CURLOPT_URL, $newurl);
    }
    return $newurl;
}


function generate_report_link($game, $id) {
    return "http://battlelog.battlefield.com/{$game}/battlereport/show/1/{$id}";
}

function gitHubAPI() {
    $client = new GithubClient();
    return $client;
};

