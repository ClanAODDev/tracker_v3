<?php
/* silence is golden */


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