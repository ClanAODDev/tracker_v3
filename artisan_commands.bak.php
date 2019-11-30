<?php
/**
 * Created by PhpStorm.
 * User: dcdeaton
 * Date: 8/4/17
 * Time: 12:40 PM
 */

/**
 * assign senior leader to all sergeants
 */
$sergeants = App\Member::whereRankId(9);
$sergeants->each(function ($member) {
    if ($member->user) {
        $member->user->assignRole(App\Role::whereName('sr_ldr')->first());
    }
});
