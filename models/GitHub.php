<?php
	class GitHub {
		private $owner = 'flashadvocate';
		private $repo = 'Division-Tracker';
		public static function client() {
				$client = new GitHubClient();
				$client->setCredentials(GITHUB_USER, GITHUB_PASS);
				$client->setPageSize(1);
				return $client;
		}
		public function getIssues() {
			$git = $this->client();
			return $git->issues->listIssues($this->owner, $this->repo);
		}
	}
?>
