<?php

class InactiveFlagged extends Application
{
    public $id;
    public $member_id;
    public $flagged_by;

    public static $id_field = 'id';
    public static $table = 'inactive_flagged';

    public static function add($params)
    {
        $flag = new self();
        $flag->member_id = $params->member_flagged;
        $flag->flagged_by = $params->flagged_by;
        $flag->save();
    }

    public static function remove($member_id)
    {
        $flag = self::find(array('member_id' => $member_id));
        Flight::aod()->remove($flag);
    }
}
