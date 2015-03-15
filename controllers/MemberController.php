<?php

class MemberController {
	public static function _doSearch() {
		$name = trim($_POST['name']);
		$results = Member::search($name);
		Flight::render('member/search', array('results' => $results));
	}

}