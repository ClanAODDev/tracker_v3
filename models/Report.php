<?php

class Report extends Application
{
    public static function findRecruitsThisMonth($game_id)
    {
        $sql = "SELECT forum_name, join_date, last_activity FROM ".Member::$table." WHERE rank_id = 1 AND status_id = 1 AND game_id = {$game_id} AND join_date <= DATE_SUB(CURRENT_DATE, INTERVAL DAYOFMONTH(CURRENT_DATE)-1 DAY) AND last_activity >= DATE_SUB(CURRENT_DATE, INTERVAL DAYOFMONTH(CURRENT_DATE)-1 DAY)";
        $params = Flight::aod()->sql($sql)->many();
        return objectToArray($params);
    }
}
