<?php

class GraphicsController {
	
	public static function _generateDivisionTop10($division) {

		$division = Division::findByName($division)->id;

		// error_reporting(E_ALL);
		// ini_set('display_errors', 1);
		header('Content-Type: image/png');
		date_default_timezone_set('America/New_York');

		$im = imagecreatetruecolor(960, 330);

		// color
		$white      = imagecolorallocate($im, 255, 255, 255);
		$grey       = imagecolorallocate($im, 128, 128, 128);
		$darkergrey = imagecolorallocate($im, 30, 30, 30);
		$orange     = imagecolorallocate($im, 255, 108, 0);
		$darkGrey   = imagecolorallocate($im, 50, 50, 50);
		$im         = imagecreatefrompng("assets/images/stats_templates/top10/big-bg.png");

		$text     = "Battlefield Division";
		$dateText = date('d M', strtotime('-30 days')) . " - " . date('d M');

		$tinyfont     = "assets/fonts/copy0855.ttf";
		$tinyboldfont = "assets/fonts/copy0866.ttf";
		$bigfont      = "assets/fonts/din-black.otf";

		// x value positions
		$games_col_1 = 190;
		$num_col_1   = 23;
		$name_col_1  = 45;

		$games_col_2 = 465;
		$num_col_2   = 300;
		$name_col_2  = 320;

		$total_count_x = 639;
		$total_count_y = 110;

		$recruits_x = 639;
		$recruits_y = 205;

		/**
		 * get data
		 */
		try {

			$daily = BfActivity::topListTodayByDivision($division);
			$monthly = BfActivity::topList30DaysByDivision($division);

			if (!$daily) {
				throw new Exception($daily['message'], 1);
			}

			if (!$monthly) {
				throw new Exception($monthly['message'], 1);
			}


		    /**
		     * create elements
		     */

    		// date
		    // imagettftext($im, 6, 0, 715, 240, $darkGrey, $tinyfont, strtoupper($dateText));    

    		// daily stats
		    $y = 65;
		    $i = 1;

		    imagettftext($im, 6, 0, $num_col_1, $y, $orange, $tinyfont, strtoupper("#"));
		    imagettftext($im, 6, 0, $name_col_1, $y, $orange, $tinyfont, strtoupper("Player"));
		    imagettftext($im, 6, 0, $games_col_1, $y, $orange, $tinyfont, strtoupper("AOD Games"));

		    foreach ($daily as $player) {
		    	$y    = $y + 20;
		    	$name = strtoupper($player->rank) . " " . strtoupper($player->forum_name);
        		// number
		    	imagettftext($im, 6, 0, $num_col_1, $y, $orange, $tinyfont, "{$i}.");
       			// name
		    	imagettftext($im, 6, 0, $name_col_1, $y, $white, $tinyfont, $name);
        		// games
		    	imagettftext($im, 6, 0, $games_col_1, $y, $white, $tinyboldfont, "{$player->aod_games}");
		    	$i++;

		    }


    		// monthly stats

		    $y = 65;
		    $i = 1;

		    imagettftext($im, 6, 0, $num_col_2, $y, $orange, $tinyfont, strtoupper("#"));
		    imagettftext($im, 6, 0, $name_col_2, $y, $orange, $tinyfont, strtoupper("Player"));
		    imagettftext($im, 6, 0, $games_col_2, $y, $orange, $tinyfont, strtoupper("AOD Games"));

		    foreach ($monthly as $player) {
		    	$y    = $y + 20;
		    	$name = strtoupper($player->rank) . " " . strtoupper($player->forum_name);
        		// number
		    	imagettftext($im, 6, 0, $num_col_2, $y, $orange, $tinyfont, "{$i}.");
        		// name
		    	imagettftext($im, 6, 0, $name_col_2, $y, $white, $tinyfont, $name);
        		// games
		    	imagettftext($im, 6, 0, $games_col_2, $y, $white, $tinyboldfont, "{$player->aod_games}");
		    	$i++;

		    }

    		// total aod games stat

		    $personnel = new stdClass();
		    $personnel->recruitsThisMonth = Division::recruitsThisMonth($division)->count;
		    $personnel->totalCount = Division::totalCount($division)->count;


		    $personnel->recruitsThisMonth = sprintf('%03d', $personnel->recruitsThisMonth);

		    imagettftext($im, 36, 0, $total_count_x, $total_count_y, $white, $bigfont, $personnel->totalCount);
		    imagettftext($im, 36, 0, $recruits_x, $recruits_y, $white, $bigfont, $personnel->recruitsThisMonth);

		}
		catch (Exception $e) {

			$im = imagecreatefrompng("assets/images/stats_templates/top10/big-bg-error.png");
			imagettftext($im, 6, 0, 10, 320, $darkGrey, $tinyfont, strtoupper($e->getMessage()));

		}


		imagepng($im);
		imagepng($im, "assets/images/toplist-cache.png");
		imagedestroy($im);
	}
}
