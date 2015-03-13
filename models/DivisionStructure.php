<?php
/**
 * Handles generation of division structure based on division
 */
class DivisionStructure {
	public static function _generate($game=2) {

    	// colors
		$division_leaders_color = "#00FF00";
		$platoon_leaders_color = "#00FF00";
		$squad_leaders_color = "#FFA500";

		$div_name_color = "#FF0000";
		$platoon_num_color = "#FF0000";
		$platoon_pos_color = "#40E0D0";

    	// misc settings
		$min_num_squad_leaders = 2;
    	// game icons
		$bf4_icon = "[img]http://i.imgur.com/WjKYT85.png[/img]";
		$bfh_icon = "[img]http://i.imgur.com/L51wBk8.png[/img]";

    	// header
		$out = "[table='width: 1000']";
		$i = 1;
		$out .= "[tr][td]";

    	// banner
		$out .= "[center][img]http://i.imgur.com/iWpjGZG.png[/img][/center]\r\n";

	    /**
	     * ---------------------------
	     * ------division leaders-----
	     * ---------------------------
	     */

	    $out .= "\r\n\r\n[center][size=5][color={$div_name_color}][b][i][u]Division Leaders[/u][/i][/b][/color][/size][/center]\r\n";
	    $out .= "[center][size=4]";
	    $divleaders = findDivisionLeaders($game);

	    foreach ($divleaders as $leader) {
	    	$aod_url = "[url=" . CLANAOD . $leader['forum_id'] . "]";
	    	$bl_url = "[url=" . BATTLELOG . $leader['battlelog_name']. "]";
	    	$out .= "{$aod_url}[color={$division_leaders_color}]{$leader['rank']} {$leader['forum_name']}[/url]{$bl_url}  {$bf4_icon}[/url][/color] - {$leader['position_desc']}\r\n";
	    }

	    $out .= "[/size][/center]\r\n\r\n";

	    /**
	     * ---------------------------
	     * -----general sergeants-----
	     * ---------------------------
	     */

	    $genSgts = findGeneralSergeants($game);
	    $out .= "[center][size=3][color={$platoon_pos_color}]General Sergeants[/color]\r\n";
	    foreach ($genSgts as $sgt) {
	    	$aod_url = "[url=" . CLANAOD . $sgt['forum_id'] . "]";
	    	$bl_url = "[url=" . BATTLELOG . $sgt['battlelog_name']. "]";
	    	$out .= "{$aod_url}{$sgt['rank']} {$sgt['forum_name']}[/url]{$bl_url}  {$bf4_icon}[/url]\r\n";
	    }
	    $out .= "[/size][/center]";
	    $out .= "[/td][/tr][/table]";


	    /**
	     * ---------------------------
	     * ---------platoons----------
	     * ---------------------------
	     */

	    $out .= "\r\n\r\n[table='width: 1100']";

	    $platoons = Platoon::find_all($game);
	    foreach ($platoons as $platoon) {
	    	if ($i == 1) {
	    		$out .= "[tr]";
	    		$out .= "[td]";
	    	} else {
	    		$out .= "[td]";
	    	}


	    	$out .= "[size=5][color={$platoon_num_color}]Platoon {$i}[/color][/size] \r\n[i][size=3]{$platoon['platoon_name']}[/size][/i]\r\n\r\n";

        	// platoon leader
	    	$leader = Platoon::findLeader($platoon['leader_id']);
	    	$aod_url = "[url=" . CLANAOD . $leader['member_id'] . "]";
	    	$bl_url = "[url=" . BATTLELOG . $leader['battlelog_name']. "]";
	    	$out .= "{$aod_url}[size=3][color={$platoon_pos_color}]Platoon Leader[/color]\r\n[color={$platoon_leaders_color}]{$leader['rank']} {$leader['forum_name']}[/color][/size][/url]{$bl_url}  {$bf4_icon}[/url]\r\n\r\n";

        	// squad leaders
	    	$squadleaders = get_squad_leaders($game, $platoon['platoon_id'], true);

	    	$mcount = 0;
	    	foreach ($squadleaders as $sqdldr) {

	    		$aod_url = "[url=" . CLANAOD . $sqdldr['member_id'] . "]";
	    		$bl_url = "[url=" . BATTLELOG . $sqdldr['battlelog_name']. "]";
	    		$out .= "[size=3][color={$platoon_pos_color}]Squad Leader[/color]\r\n{$aod_url}[color={$squad_leaders_color}]{$sqdldr['rank']} {$sqdldr['name']}[/color][/url]{$bl_url}  {$bf4_icon}[/url][/size]\r\n";

            	// squad members
	    		$squadmembers = get_my_squad($sqdldr['member_id'], true);
	    		$out .= "[size=1][list=1]";

	    		foreach ($squadmembers as $member) {
	    			$aod_url = "[url=" . CLANAOD . $member['member_id'] . "]";  
	    			$bl_url = "[url=" . BATTLELOG . $member['battlelog_name']. "]";
	    			$out .= "[*]{$aod_url}{$member['rank']} {$member['forum_name']}[/url]{$bl_url}  {$bf4_icon}[/url]\r\n";
	    		}

	    		$out .= "[/list][/size]\r\n";
	    		$mcount++;
	    	}

	    	if ($mcount < $min_num_squad_leaders) {
            	// minimum of 2 squad leaders per platoon
	    		$min_num_squad_leaders = ($min_num_squad_leaders < 2) ? 2 : $min_num_squad_leaders;
	    		for ($mcount = $mcount; $mcount < $min_num_squad_leaders; $mcount++)
	    			$out .= "[size=3][color={$platoon_pos_color}]Squad Leader[/color]\r\n[color={$squad_leaders_color}]TBA[/color][/size]\r\n";
	    	}

	    	$out .= "\r\n\r\n";

	        /**
	         * ---------------------------
	         * ----general population-----
	         * ---------------------------
	         */

	        $genpop = get_gen_pop($platoon['platoon_id'], true);
	        $out .= "[size=3][color={$platoon_pos_color}]Members[/color][/size]\r\n[size=1]";
	        foreach ($genpop as $member) {
	        	$bl_url = "[url=" . BATTLELOG . $member['battlelog_name']. "]";
	        	$aod_url = "[url=" . CLANAOD . $member['member_id'] . "]";
	        	$out .= "{$aod_url}{$member['rank']} {$member['forum_name']}[/url]{$bl_url}  {$bf4_icon}[/url]\r\n";

	        }

	        $out .= "[/size]";
	        $out .= "[/td]";

	        $i++;

	    }
    	// end last platoon
	    $out .= "[/tr][/table]\r\n\r\n";

	    /**
	     * ---------------------------
	     * --------part timers--------
	     * ---------------------------
	     */
	    $i = 1;

	    $out .= "\r\n[table='width: 1000']";
	    $out .= "[tr][td]\r\n[center][size=3][color={$platoon_pos_color}][b]Part Time Members[/b][/color][/size][/center][/td][/tr]";
	    $out .= "[/table]\r\n\r\n";
	    $out .= "[table='width: 1000']";
	    $out .= "[tr][td][center]";


	    $partTimers = get_part_timers($game);
	    foreach ($partTimers as $member) {

	    	if ($i % 10 == 0) {
	    		$out .= "[/td][td]";
	    	}
	    	$bl_url = "[url=" . BATTLELOG . $member['battlelog_name']. "]";
	    	$aod_url = "[url=" . CLANAOD . $member['member_id'] . "]";
	    	$out .= "{$aod_url}AOD_{$member['forum_name']}[/url]{$bl_url}  {$bf4_icon}[/url]\r\n";

	    	$i++;

	    }

	    $out .= "[/center][/td]";
	    $out .= "[/tr][/table]\r\n\r\n";


	    /**
	     * ---------------------------
	     * -----------LOAS------------
	     * ---------------------------
	     */

	    $i = 1;

	    $out .= "\r\n[table='width: 1000']";
	    $out .= "[tr][td]\r\n[center][size=3][color={$platoon_pos_color}][b]Leaves of Absence[/b][/color][/size][/center][/td][/tr]";
	    $out .= "[/table]\r\n\r\n";
	    $out .= "[table='width: 1000']";
	    $out .= "[tr][td][center]";


	    $loas = get_approved_loas($game);
	    foreach ($loas as $member) {
	    	$date_end = date("M d, Y", strtotime($member['date_end']));
	    	$aod_url = "[url=" . CLANAOD . $member['member_id'] . "]";
	    	$out .= "{$aod_url}{$member['rank']} {$member['forum_name']}[/url]\r\n[b]Ends[/b] {$date_end}\r\n{$member['reason']}\r\n\r\n";

	    	$i++;

	    }

	    $out .= "[/center][/td]";
	    $out .= "[/tr][/table]";

	    return $out;
	}
}