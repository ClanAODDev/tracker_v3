<?php
	class GitHub {

		private static $owner = 'flashadvocate';
		private static $repo = 'Division-Tracker';

		private static function client() {
				$client = new GitHubClient();
				$client->setCredentials(GITHUB_USER, GITHUB_PASS);
				$client->setPageSize(1);
				return $client;
		}

		public static function getIssues() {
			$git = self::client();
			return $git->issues->listIssues(self::$owner, self::$repo);
		}

		public static function createIssue($title, $body) {
			$git = self::client();
			$git->issues->createAnIssue(self::$owner, self::$repo, $title, $body);
		}
		
	}