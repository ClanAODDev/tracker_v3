<?php

class BfDivisionStructure {

	function __construct($game_id) {

		$this->game_id = $game_id;

		// get data
		$this->division = Division::findById($this->game_id);
		$this->platoons = Platoon::find_all($this->game_id);

		// colors
		$this->division_leaders_color = "#00FF00";
		$this->platoon_leaders_color = "#00FF00";
		$this->squad_leaders_color = "#FFA500";
		$this->div_name_color = "#FF0000";
		$this->platoon_num_color = "#FF0000";
		$this->platoon_pos_color = "#40E0D0";

		// number of columns
		$this->num_columns = 3;

		// widths
		$this->players_width = 900;
		$this->info_width = 800;

    	// misc settings
		$this->min_num_squad_leaders = 2;

		self::generate();

	}


	public function generate() {
		
    	// header
		$division_structure = "[table='width: {$this->info_width}']";
		$division_structure .= "[tr][td]";

    	// banner
		$division_structure .= "[center][img]http://i.imgur.com/iWpjGZG.png[/img][/center]\r\n";

	    /**
	     * ------division leaders-----
	     */

	    $division_structure .= "\r\n\r\n[center][size=5][color={$this->div_name_color}][b][i][u]Division Leaders[/u][/i][/b][/color][/size][/center]\r\n";
	    $division_structure .= "[center][size=4]";

	    $division_leaders = Division::findDivisionLeaders($this->game_id);
	    foreach ($division_leaders as $leader) {
	    	$leader_name = Rank::convert($leader->rank_id)->abbr." ".$leader->forum_name;
	    	$memberHandle = MemberHandle::findHandle($leader->id, $this->division->primary_handle);

	    	$leader->handle = $memberHandle->handle_value;

	    	$aod_url = "[url=" . CLANAOD . $leader->member_id . "]";
	    	$bl_url = "[url=" . $memberHandle->url .  $leader->handle. "][BL][/url]";
	    	$division_structure .= "{$aod_url}[color={$this->division_leaders_color}]{$leader_name}[/url] {$bl_url}[/color] - {$leader->position_desc}\r\n";
	    }

	    $division_structure .= "[/size][/center]\r\n\r\n";

		/**
	     * -----general sergeants-----
	     */

		$genSgts = Division::findGeneralSergeants($this->game_id);
		$division_structure .= "[center][size=3][color={$this->platoon_pos_color}]General Sergeants[/color]\r\n";

		foreach ($genSgts as $sgt) {
			$sgt_name = Rank::convert($sgt->rank_id)->abbr." ".$sgt->forum_name;
			$memberHandle = MemberHandle::findHandle($sgt->id, $this->division->primary_handle);
			$sgt->handle = $memberHandle->handle_value;

			$aod_url = "[url=" . CLANAOD . $sgt->member_id . "]";
			$bl_url = "[url=" . $memberHandle->url .  $sgt->handle. "][BL][/url]";
			$division_structure .= "{$aod_url}{$sgt_name}[/url] {$bl_url}\r\n";
		}
		$division_structure .= "[/size][/center]";
		$division_structure .= "[/td][/tr][/table]";

		/**
	     * ---------platoons----------
	     */

		$division_structure .= "\r\n\r\n[table='width: {$this->players_width}']";
		$platoons = $this->platoons;
		$i = 1;

		foreach ($platoons as $platoon) {

			$countMembers = Platoon::countPlatoon($platoon->id);

			if ($i == 1) {
				$division_structure .= "[tr]";
				$division_structure .= "[td]";
			} else {
				$division_structure .= "[td]";
			}

			$division_structure .= "[size=5][color={$this->platoon_num_color}]". ordsuffix($i) ." Platoon[/color][/size] \r\n[i][size=3]{$platoon->name} [/size][/i]\r\n\r\n";

    		// platoon leaders
			$leader = Member::findByMemberId($platoon->leader_id);

			if ($platoon->leader_id != 0) {
				$memberHandle = MemberHandle::findHandle($leader->id, $this->division->primary_handle);
				$leader->handle = $memberHandle->handle_value;

				$aod_url = "[url=" . CLANAOD . $leader->member_id . "]";
				$bl_url = "[url=" . $memberHandle->url .  $leader->handle. "][BL][/url]";
			}

			if ($platoon->leader_id != 0) {
				$leader_name = Rank::convert($leader->rank_id)->abbr. " " .ucwords($leader->forum_name);
				$division_structure .= "{$aod_url}[size=3][color={$this->platoon_pos_color}]Platoon Leader[/color]\r\n[color={$this->platoon_leaders_color}]{$leader_name}[/color][/size][/url] {$bl_url}\r\n\r\n";
			} else {
				$division_structure .= "[size=3][color={$this->platoon_pos_color}]Platoon Leader[/color]\r\n[color={$this->platoon_leaders_color}]TBA[/color][/size]\r\n\r\n";
			}

    		// squad leaders
			$squads = Squad::findAll($this->game_id, $platoon->id);

			foreach ($squads as $squad) {

				if ($squad->leader_id != 0) {
					$leader = Member::findById($squad->leader_id);
					$memberHandle = MemberHandle::findHandle($leader->id, $this->division->primary_handle);
					$leader->handle = $memberHandle->handle_value;
					$aod_url = "[url=" . CLANAOD . $leader->member_id . "]";
					$bl_url = "[url=" . $memberHandle->url .  $leader->handle. "][BL][/url]";

					$division_structure .= "[size=3][color={$this->platoon_pos_color}]Squad Leader[/color]\r\n{$aod_url}[color={$this->squad_leaders_color}]" . Rank::convert($leader->rank_id)->abbr . " {$leader->forum_name}[/color][/url] {$bl_url}[/size]\r\n\r\n";
					$division_structure .= "[size=1]";

					// direct recruits
					$recruits = arrayToObject(Member::findRecruits($leader->member_id, $leader->platoon_id, true));

					if (count((array) $recruits)) {
						$division_structure .= "[list=1]";

						foreach ($recruits as $recruit) { 

							$memberHandle = MemberHandle::findHandle($recruit->id, $this->division->primary_handle);
							$division_structure .= "[*]{$aod_url}" . Rank::convert($recruit->rank_id)->abbr . " {$recruit->forum_name}[/url]";

							// does member have a member handle?
							if (count((array)$memberHandle)) {
								$recruit->handle = (is_object($memberHandle)) ? $memberHandle->handle_value : NULL;
								$url = (is_object($memberHandle)) ? $memberHandle->url : NULL;
								$aod_url = "[url=" . CLANAOD . $recruit->member_id . "]";
								$bl_url = "[url=" . $url .  $recruit->handle. "][BL][/url]";
								$division_structure .= "{$bl_url}\r\n";
							} else {
								$division_structure .= " [color=red]XX[/color]\r\n";
							}

						}

						$division_structure .= "[/list]";

					}

				} else {

					$division_structure .= "[size=3][color={$this->platoon_pos_color}]Squad Leader[/color]\r\n[color={$this->squad_leaders_color}]TBA[/color][/size]\r\n";
					$division_structure .= "[size=1]";

				}

				$division_structure .= "\r\n";

        		// squad members
				$squadMembers = arrayToObject(Squad::findSquadMembers($squad->id, true, $leader->member_id));
				foreach ($squadMembers as $player) {
					$memberHandle = MemberHandle::findHandle($player->id, $this->division->primary_handle);
					$player->handle = $memberHandle->handle_value;
					$aod_url = "[url=" . CLANAOD . $player->member_id . "]";  
					$bl_url = "[url=" . $memberHandle->url .  $player->handle. "][BL][/url]";
					$division_structure .= "{$aod_url}" . Rank::convert($player->rank_id)->abbr . " {$player->forum_name}[/url] {$bl_url}\r\n";
				}

				$division_structure .= "[/size]\r\n";

			}

			$division_structure .= "\r\n\r\n";

			if ($i % $this->num_columns == 0) {
				$division_structure .= "[/td][/tr][tr]";	
			}
			$division_structure .= "[/td]";

			$i++;
		}

    	// end last platoon
		$division_structure .= "[/tr][/table]\r\n\r\n";

		/**
	     * --------part timers--------
	     */

		$i = 1;

		// header
		$division_structure .= "\r\n[table='width: {$this->info_width}']";
		$division_structure .= "[tr][td]\r\n[center][size=3][color={$this->platoon_pos_color}][b]Part Time Members[/b][/color][/size][/center][/td][/tr]";
		$division_structure .= "[/table]\r\n\r\n";

		// players
		$division_structure .= "[table='width: {$this->info_width}']";
		$division_structure .= "[tr][td]";

		$partTimers = PartTime::find_all($this->game_id);

		foreach ($partTimers as $player) {

			if ($i % 20 == 0) {
				$division_structure .= "[/td][td]";
			}
			$bl_url = "[url=" . BATTLELOG .  $player->ingame_alias . "][BL][/url]";
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

		// header
		$division_structure .= "\r\n[table='width: {$this->info_width}']";
		$division_structure .= "[tr][td]\r\n[center][size=3][color={$this->platoon_pos_color}][b]Leaves of Absence[/b][/color][/size][/center][/td][/tr]";
		$division_structure .= "[/table]\r\n\r\n";

		// players
		$division_structure .= "[table='width: {$this->info_width}']";
		$loas = LeaveOfAbsence::find_all($this->game_id);

		foreach ($loas as $player) {
			$date_end = (strtotime($player->date_end) < strtotime('now')) ? "[COLOR='#FF0000']Expired " . formatTime(strtotime($player->date_end)) . "[/COLOR]" : date("M d, Y", strtotime($player->date_end)); 
			$aod_url = "[url=" . CLANAOD . $player->member_id . "]";
			$profile = Member::findByMemberId($player->member_id);

			$division_structure .= "[tr][td]{$aod_url}" . Member::findForumName($profile->member_id) . "[/url][/td][td][center]{$date_end}[/center][/td][td]{$player->reason}[/td][/tr]";
			$i++;
		}
		
		$division_structure .= "[/table]";

		$this->content = $division_structure;
	}
}