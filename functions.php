<?php

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
        $role = "Division Commander";
        break;
        case 4:
        $role = "Administrator";
        break;
    }
    return $role;
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

/*
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
}*/