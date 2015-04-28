<?php

class DivisionStructure {

	public static function generate($member) {
		
		$division = Division::findById($member->game_id);
		$platoons = Platoon::find_all($member->game_id);

		// colors
		$division_leaders_color = "#00FF00";
		$platoon_leaders_color = "#00FF00";
		$squad_leaders_color = "#FFA500";
		$div_name_color = "#FF0000";
		$platoon_num_color = "#FF0000";
		$platoon_pos_color = "#40E0D0";

		// widths
		$players_width = 1300;
		$info_width = 1200;

    	// misc settings
		$min_num_squad_leaders = 2;

		// ctr
		$i = 1;

    	// game icons
		$bf4_icon = "[img]http://i.imgur.com/WjKYT85.png[/img]";
		$bfh_icon = "[img]http://i.imgur.com/L51wBk8.png[/img]";

    	// header
		$division_structure = "[table='width: {$info_width}']";
		$division_structure .= "[tr][td]";

    	// banner
		$division_structure .= "[center][img]http://i.imgur.com/iWpjGZG.png[/img][/center]\r\n";

	    /**
	     * ------division leaders-----
	     */

	    $division_structure .= "\r\n\r\n[center][size=5][color={$div_name_color}][b][i][u]Division Leaders[/u][/i][/b][/color][/size][/center]\r\n";
	    $division_structure .= "[center][size=4]";

	    $division_leaders = Division::findDivisionLeaders($member->game_id);
	    foreach ($division_leaders as $leader) {
	    	$aod_url = "[url=" . CLANAOD . $leader->member_id . "]";
	    	$bl_url = "[url=" . BATTLELOG . $leader->battlelog_name. "]";
	    	$division_structure .= "{$aod_url}[color={$division_leaders_color}]{$leader->rank} {$leader->forum_name}[/url] {$bl_url}{$bf4_icon}[/url][/color] - {$leader->position_desc}\r\n";
	    }

	    $division_structure .= "[/size][/center]\r\n\r\n";

	    /**
	     * -----general sergeants-----
	     */

	    $genSgts = Division::findGeneralSergeants($member->game_id);
	    $division_structure .= "[center][size=3][color={$platoon_pos_color}]General Sergeants[/color]\r\n";
	    foreach ($genSgts as $sgt) {
	    	$aod_url = "[url=" . CLANAOD . $sgt->forum_id . "]";
	    	$bl_url = "[url=" . BATTLELOG . $sgt->battlelog_name. "]";
	    	$division_structure .= "{$aod_url}{$sgt->rank} {$sgt->forum_name}[/url] {$bl_url}{$bf4_icon}[/url]\r\n";
	    }
	    $division_structure .= "[/size][/center]";

	    $division_structure .= "[/td][/tr][/table]";

	    /**
	     * ---------platoons----------
	     */

	    $division_structure .= "\r\n\r\n[table='width: {$players_width}']";
	    $platoons = Platoon::find_all($member->game_id);

	    foreach ($platoons as $platoon) {

	    	$countMembers = Platoon::countPlatoon($platoon->id);

	    	if ($i == 1) {
	    		$division_structure .= "[tr]";
	    		$division_structure .= "[td]";
	    	} else {
	    		$division_structure .= "[td]";
	    	}

	    	$division_structure .= "[size=5][color={$platoon_num_color}]Platoon {$i}[/color][/size] \r\n[i][size=3]{$platoon->name} [/size][/i]\r\n\r\n";

        	// platoon leader
	    	$leader = Member::profileData($platoon->leader_id);
	    	$aod_url = "[url=" . CLANAOD . $leader->member_id . "]";
	    	$bl_url = "[url=" . BATTLELOG . $leader->battlelog_name. "]";
	    	$division_structure .= "{$aod_url}[size=3][color={$platoon_pos_color}]Platoon Leader[/color]\r\n[color={$platoon_leaders_color}]{$leader->rank} {$leader->forum_name}[/color][/size][/url] {$bl_url}{$bf4_icon}[/url]\r\n\r\n";

       		// squad leaders
	    	$squadleaders = Platoon::SquadLeaders($member->game_id, $platoon->id, true);
	    	$mcount = 0;

	    	foreach ($squadleaders as $sqdldr) {
	    		$aod_url = "[url=" . CLANAOD . $sqdldr->member_id . "]";
	    		$bl_url = "[url=" . BATTLELOG . $sqdldr->battlelog_name. "]";
	    		$division_structure .= "[size=3][color={$platoon_pos_color}]Squad Leader[/color]\r\n{$aod_url}[color={$squad_leaders_color}]{$sqdldr->abbr} {$sqdldr->forum_name}[/color][/url] {$bl_url}{$bf4_icon}[/url][/size]\r\n";

            	// squad members
	    		$squadmembers = Squad::find($sqdldr->member_id, true);
	    		$division_structure .= "[size=1][list=1]";

	    		foreach ($squadmembers as $player) {
	    			$aod_url = "[url=" . CLANAOD . $player->member_id . "]";  
	    			$bl_url = "[url=" . BATTLELOG . $player->battlelog_name. "]";
	    			$division_structure .= "[*]{$aod_url}{$player->rank} {$player->forum_name}[/url] {$bl_url}{$bf4_icon}[/url]\r\n";
	    		}

	    		$division_structure .= "[/list][/size]\r\n";
	    		$mcount++;
	    	}

	    	if ($mcount < $min_num_squad_leaders) {
            // minimum of 2 squad leaders per platoon
	    		$min_num_squad_leaders = ($min_num_squad_leaders < 2) ? 2 : $min_num_squad_leaders;
	    		for ($mcount = $mcount; $mcount < $min_num_squad_leaders; $mcount++)
	    			$division_structure .= "[size=3][color={$platoon_pos_color}]Squad Leader[/color]\r\n[color={$squad_leaders_color}]TBA[/color][/size]\r\n";
	    	}

	    	$division_structure .= "\r\n\r\n";

	        /**
	         * ----general population-----
	         */
	        
	        $genpop = Platoon::GeneralPop($platoon->id, true);
	        $division_structure .= "[size=3][color={$platoon_pos_color}]Members[/color][/size]\r\n[size=1]";

	        foreach ($genpop as $player) {
	        	$bl_url = "[url=" . BATTLELOG . $player->battlelog_name. "]";
	        	$aod_url = "[url=" . CLANAOD . $player->member_id . "]";
	        	$division_structure .= "{$aod_url}{$player->rank} {$player->forum_name}[/url] {$bl_url}{$bf4_icon}[/url]\r\n";
	        }

	        $division_structure .= "[/size]";
	        $division_structure .= "[/td]";

	        $i++;
	    }
	    
    	// end last platoon
	    $division_structure .= "[/tr][/table]\r\n\r\n";

	    /**
	     * --------part timers--------
	     */
	    
	    $i = 1;

	    $division_structure .= "\r\n[table='width: {$info_width}']";
	    $division_structure .= "[tr][td]\r\n[center][size=3][color={$platoon_pos_color}][b]Part Time Members[/b][/color][/size][/center][/td][/tr]";
	    $division_structure .= "[/table]\r\n\r\n";
	    $division_structure .= "[table='width: {$info_width}']";
	    $division_structure .= "[tr][td]";

	    $partTimers = PartTime::find_all($member->game_id);

	    foreach ($partTimers as $player) {
	    	if ($i % 10 == 0) {
	    		$division_structure .= "[/td][td]";
	    	}
	    	$bl_url = "[url=" . BATTLELOG . $player->battlelog_name. "]";
	    	$aod_url = "[url=" . CLANAOD . $player->member_id . "]";
	    	$division_structure .= "{$aod_url}AOD_{$player->forum_name}[/url] {$bl_url}{$bf4_icon}[/url]\r\n";
	    	$i++;
	    }
	    $division_structure .= "[/td]";
	    $division_structure .= "[/tr][/table]\r\n\r\n";

	    /**
	     * -----------LOAS------------
	     */

	    $i = 1;

	    $division_structure .= "\r\n[table='width: {$info_width}']";
	    $division_structure .= "[tr][td]\r\n[center][size=3][color={$platoon_pos_color}][b]Leaves of Absence[/b][/color][/size][/center][/td][/tr]";
	    $division_structure .= "[/table]\r\n\r\n";
	    $division_structure .= "[table='width: {$info_width}']";
	    $division_structure .= "[tr][td][center]";
	    $loas = LeaveOfAbsence::find_all($member->game_id);
	    foreach ($loas as $player) {
	    	if ($i % 10 == 0) {
	    		$division_structure .= "[/td][td]";
	    	}
	    	$date_end = date("M d, Y", strtotime($player->date_end));
	    	$aod_url = "[url=" . CLANAOD . $player->member_id . "]";
	    	$division_structure .= "{$aod_url}" . Member::findForumName($player->member_id) . "[/url] -- {$date_end} -- {$player->reason}\r\n";
	    	$i++;
	    }
	    $division_structure .= "[/center][/td]";
	    $division_structure .= "[/tr][/table]";

	    return $division_structure;
	}
}