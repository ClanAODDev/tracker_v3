<?php 

class BfActivity extends Application
{
    public $id;
    public $member_id;
    public $server;
    public $datetime;
    public $hash;
    public $game_id;
    public $map_name;
    public $report_id;

    public static $table = 'bf_activity';
    public static $id_field = 'id';
    public static $name_field = 'server';

    public static function find_allGames($member_id, $limit=MAX_GAMES_ON_PROFILE)
    {
        $sql = "SELECT * FROM ".self::$table." WHERE member_id = {$member_id} ORDER BY datetime DESC LIMIT {$limit}";
        return Flight::aod()->sql($sql)->many();
    }

    public static function countPlayerGames($member_id, $bdate, $edate)
    {
        $sql = "SELECT count(*) as count FROM ".self::$table." WHERE member_id = {$member_id} AND datetime between '{$bdate}' AND '{$edate}'";
        $params = Flight::aod()->sql($sql)->one();
        return $params['count'];
    }

    public static function countPlayerAODGames($member_id, $bdate, $edate)
    {
        $sql = "SELECT count(*) as count FROM ".self::$table." WHERE member_id = {$member_id} AND server LIKE '%aod%' AND datetime between '{$bdate}' AND '{$edate}'";
        $params = Flight::aod()->sql($sql)->one();
        return $params['count'];
    }

    public static function findTotalGamesByArray($params, $bdate, $edate)
    {
        foreach ($params as $member) {
            $sql = "SELECT count(*) as count FROM ".self::$table." WHERE member_id = {$member} AND datetime between '{$bdate}' AND '{$edate}'";
            $params = Flight::aod()->sql($sql)->one();
            foreach ($params as $count) {
                $games[] = $count;
            }
        }
        return $games;
    }

    public static function findTotalAODGamesByArray($params, $bdate, $edate)
    {
        foreach ($params as $member) {
            $sql = "SELECT count(*) as count FROM ".self::$table." WHERE member_id = {$member} AND server LIKE '%aod%' AND datetime between '{$bdate}' AND '{$edate}'";
            $params = Flight::aod()->sql($sql)->one();
            foreach ($params as $count) {
                $games[] = $count;
            }
        }
        return $games;
    }

    public static function topList30DaysByDivision($game_id)
    {
        $sql = "SELECT forum_name, rank_id, p.number as plt, member_id, ( SELECT count(*) FROM ".self::$table." a WHERE a.member_id = m.member_id AND a.server LIKE 'AOD%' AND a.datetime BETWEEN DATE_SUB(NOW(), INTERVAL 30 day) AND CURRENT_TIMESTAMP ) AS aod_games FROM ".Member::$table." m LEFT JOIN ".Platoon::$table." p ON m.platoon_id = p.id WHERE m.game_id = {$game_id} AND (status_id = 1 OR status_id = 999) ORDER BY aod_games DESC LIMIT 10";
        return arrayToObject(Flight::aod()->sql($sql)->many());
    }

    public static function topListTodayByDivision($game_id)
    {
        $sql = "SELECT forum_name, rank_id, p.number as plt, member_id, ( SELECT count(*) FROM ".self::$table." a WHERE a.member_id = m.member_id AND a.server LIKE 'AOD%' AND a.datetime BETWEEN DATE_SUB(NOW(), INTERVAL 1 day) AND CURRENT_TIMESTAMP ) AS aod_games FROM ".Member::$table." m LEFT JOIN ".Platoon::$table." p ON m.platoon_id = p.id WHERE m.game_id = {$game_id} AND (status_id = 1 OR status_id = 999) ORDER BY aod_games DESC LIMIT 10";
        return arrayToObject(Flight::aod()->sql($sql)->many());
    }

    public static function toplistMonthlyAODTotal()
    {
        $sql = "SELECT round((SELECT count(*) FROM ".self::$table." a WHERE (server LIKE '%AOD%') AND a.datetime BETWEEN DATE_SUB(NOW(), INTERVAL 30 day) AND CURRENT_TIMESTAMP) / count(*)*100, 1) as pct FROM ".self::$table." a WHERE a.datetime BETWEEN DATE_SUB( NOW(), INTERVAL 30 day ) AND CURRENT_TIMESTAMP";
        $params = Flight::aod()->sql($sql)->one();
        return $params['pct'];
    }
}
