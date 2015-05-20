<?php

/** 
 * github api interface
 */

require_once(__DIR__ . '/client/GitHubClient.php');
require('credentials.php');

$owner = 'flashadvocate';
$repo = 'aod_rct';
$title = 'Something is broken.'
$body = 'Please fix it.'.

$client = new GitHubClient();
$client->setCredentials($username, $password);
$client->issues->createAnIssue($owner, $repo, $title, $body);