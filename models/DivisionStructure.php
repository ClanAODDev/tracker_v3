<?php

class DivisionStructure {

	public static function getIcons($array) {
		$string = NULL;
		foreach ($array as $game) {
			$string .= convertIcon($game['short_name']);
		}
		return $string;
	}

	public static function generate($game_id) {
		
		$division = Division::findById($game_id);
		$platoons = Platoon::find_all($game_id);

		// colors
		$division_leaders_color = "#00FF00";
		$platoon_leaders_color = "#00FF00";
		$squad_leaders_color = "#FFA500";
		$div_name_color = "#FF0000";
		$platoon_num_color = "#FF0000";
		$platoon_pos_color = "#40E0D0";

		// widths
		$players_width = 1500;
		$info_width = 1400;

    	// misc settings
		$min_num_squad_leaders = 2;

		// ctr
		$i = 1;

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

	    $division_leaders = Division::findDivisionLeaders($game_id);
	    foreach ($division_leaders as $leader) {
	    	$games = self::getIcons(MemberGame::getGamesPlayed($leader->id));
	    	$aod_url = "[url=" . CLANAOD . $leader->member_id . "]";
	    	$bl_url = "[url=" . BATTLELOG . $leader->battlelog_name. "]{$games}[/url]";
	    	$division_structure .= "{$aod_url}[color={$division_leaders_color}]{$leader->rank} {$leader->forum_name}[/url] {$bl_url}[/color] - {$leader->position_desc}\r\n";
	    }

	    $division_structure .= "[/size][/center]\r\n\r\n";

		/**
	     * -----general sergeants-----
	     */

		$genSgts = Division::findGeneralSergeants($game_id);
		$division_structure .= "[center][size=3][color={$platoon_pos_color}]General Sergeants[/color]\r\n";
		foreach ($genSgts as $sgt) {
			$games = self::getIcons(MemberGame::getGamesPlayed($sgt->id));
			$aod_url = "[url=" . CLANAOD . $sgt->forum_id . "]";
			$bl_url = "[url=" . BATTLELOG . $sgt->battlelog_name. "]{$games}[/url]";

			$division_structure .= "{$aod_url}{$sgt->rank} {$sgt->forum_name}[/url] {$bl_url}\r\n";
		}
		$division_structure .= "[/size][/center]";
		$division_structure .= "[/td][/tr][/table]";

		/**
	     * ---------platoons----------
	     */

		$division_structure .= "\r\n\r\n[table='width: {$players_width}']";
		$platoons = Platoon::find_all($game_id);

		foreach ($platoons as $platoon) {

			$countMembers = Platoon::countPlatoon($platoon->id);

			if ($i == 1) {
				$division_structure .= "[tr]";
				$division_structure .= "[td]";
			} else {
				$division_structure .= "[td]";
			}

			$division_structure .= "[size=5][color={$platoon_num_color}]". ordsuffix($i) ." Platoon[/color][/size] \r\n[i][size=3]{$platoon->name} [/size][/i]\r\n\r\n";

    		// platoon leaders
			$leader = Member::findByMemberId($platoon->leader_id);

			if ($platoon->leader_id != 0) {
				$games = self::getIcons(MemberGame::getGamesPlayed($leader->id));
				$aod_url = "[url=" . CLANAOD . $leader->member_id . "]";
				$bl_url = "[url=" . BATTLELOG . $leader->battlelog_name. "]{$games}[/url]";
			}

			if ($platoon->leader_id != 0) {
				$leader_name = Rank::convert($leader->rank_id)->abbr. " " .ucwords($leader->forum_name);
				$division_structure .= "{$aod_url}[size=3][color={$platoon_pos_color}]Platoon Leader[/color]\r\n[color={$platoon_leaders_color}]{$leader_name}[/color][/size][/url] {$bl_url}\r\n\r\n";
			} else {
				$division_structure .= "[size=3][color={$platoon_pos_color}]Platoon Leader[/color]\r\n[color={$platoon_leaders_color}]TBA[/color][/size]\r\n\r\n";
			}


    		// squad leaders
			$squads = Squad::findAll($game_id, $platoon->id);

			foreach ($squads as $squad) {

				if ($squad->leader_id != 0) {

					$leader = Member::findById($squad->leader_id);
					$games = self::getIcons(MemberGame::getGamesPlayed($leader->id));
					$aod_url = "[url=" . CLANAOD . $leader->member_id . "]";
					$bl_url = "[url=" . BATTLELOG . $leader->battlelog_name. "]{$games}[/url]";

					$division_structure .= "[size=3][color={$platoon_pos_color}]Squad Leader[/color]\r\n{$aod_url}[color={$squad_leaders_color}]" . Rank::convert($leader->rank_id)->abbr . " {$leader->forum_name}[/color][/url] {$bl_url}[/size]\r\n\r\n";
					$division_structure .= "[size=1]";

					// direct recruits
					$recruits = arrayToObject(Member::findRecruits($leader->member_id, true));
					if (count((array) $recruits)) {
						$division_structure .= "[list=1]";

						foreach ($recruits as $player) {
							$games = self::getIcons(MemberGame::getGamesPlayed($player->id));
							$aod_url = "[url=" . CLANAOD . $player->member_id . "]";  
							$bl_url = "[url=" . BATTLELOG . $player->battlelog_name. "]{$games}[/url]";
							$division_structure .= "[*]{$aod_url}" . Rank::convert($player->rank_id)->abbr . " {$player->forum_name}[/url] {$bl_url}\r\n";
						}
						$division_structure .= "[/list]";

					}

				} else {

					$division_structure .= "[size=3][color={$platoon_pos_color}]Squad Leader[/color]\r\n[color={$squad_leaders_color}]TBA[/color][/size]\r\n";
					$division_structure .= "[size=1]";

				}

				$division_structure .= "\r\n";

        		// squad members
				$squadMembers = arrayToObject(Squad::findSquadMembers($squad->id, true, $leader->member_id));
				foreach ($squadMembers as $player) {
					$games = self::getIcons(MemberGame::getGamesPlayed($player->id));
					$aod_url = "[url=" . CLANAOD . $player->member_id . "]";  
					$bl_url = "[url=" . BATTLELOG . $player->battlelog_name. "]{$games}[/url]";
					$division_structure .= "{$aod_url}" . Rank::convert($player->rank_id)->abbr . " {$player->forum_name}[/url] {$bl_url}\r\n";
				}

				$division_structure .= "[/size]\r\n";

			}

			$division_structure .= "\r\n\r\n";
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

		$partTimers = PartTime::find_all($game_id);

		foreach ($partTimers as $player) {
			if ($i % 15 == 0) {
				$division_structure .= "[/td][td]";
			}
			$bl_url = "[url=" . BATTLELOG . $player->battlelog_name. "][BL][/url]";
			$aod_url = "[url=" . CLANAOD . $player->member_id . "]";
			$division_structure .= "{$aod_url}AOD_{$player->forum_name}[/url] {$bl_url}\r\n";
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
		$division_structure .= "[tr][td]";
		$loas = LeaveOfAbsence::find_all($game_id);
		foreach ($loas as $player) {
			if ($i % 15 == 0) {
				$division_structure .= "[/td][td]";
			}

			$date_end = (strtotime($player->date_end) < strtotime('now')) ? "[COLOR='#FF0000']Expired " . formatTime(strtotime($player->date_end)) . "[/COLOR]" : date("M d, Y", strtotime($player->date_end)); 
			

			$aod_url = "[url=" . CLANAOD . $player->member_id . "]";
			$profile = Member::findByMemberId($player->member_id);
			$division_structure .= "{$aod_url}" . Member::findForumName($profile->member_id) . "[/url] -- {$date_end} -- {$player->reason}\r\n";
			$i++;
		}
		
		$division_structure .= "[/td]";
		$division_structure .= "[/tr][/table]";

		return $division_structure;
	}
}