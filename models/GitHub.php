<?php
class GitHub extends Application
{
    private static $owner = 'flashadvocate';
    private static $repo = 'Division-Tracker';

    private static function client()
    {
        $client = new GitHubClient();
        $client->setCredentials(GITHUB_USER, GITHUB_PASS);
        return $client;
    }

    /**
     * List all issues
     * @param $owner boolean|string true, for all my issues, false, for all issues or organization name all issues
     * @param filter string	Indicates which sorts of issues to return. Can be one of:
     * 					assigned: Issues assigned to you
     * 					created: Issues created by you
     * 					mentioned: Issues mentioning you
     * 					subscribed: Issues you’re subscribed to updates for
     * 					all: All issues the authenticated user can see, regardless of participation or creation
     * 					Default: assigned
     * @param state string	Indicates the state of the issues to return. Can be either open, closed, or all. Default: open
     * @param labels string	A list of comma separated label names. Example: bug,ui,@high
     * @param sort string	What to sort results by. Can be either created, updated, comments. Default: created
     * @param direction string	The direction of the sort. Can be either asc or desc. Default: desc
     * @param since string	Only issues updated at or after this time are returned. This is a timestamp in ISO 8601 format: YYYY-MM-DDTHH:MM:SSZ.
     * @return array<GitHubIssue>
     */

    public static function createIssue($title, $body)
    {
        try {
            $git = self::client();
            $issue = $git->issues->createAnIssue(self::$owner, self::$repo, $title, $body, null, null, array("client"));
            return $issue;
        } catch (GitHubClientException $e) {
            return $e->getMessage();
        }
    }
}
