<?php

class GithubController
{

    public static function _createIssue()
    {
        Flight::render('modals/create_issue', array('js' => 'issue'));
    }

    public static function _doSubmitIssue()
    {

        if (!empty($_POST['title'])) {

            $user = Member::findById($_POST['user'])->forum_name;
            $title = $_POST['title'];
            $link = $_POST['link'];
            $body = $_POST['body'];
            $body .= (!empty($link)) ? "<hr /><strong>Page reported</strong>: {$link}<br />" : null;
            $body .= "<strong>Reported by</strong>: {$user}";
            $issue = GitHub::createIssue($title, $body);

            if (is_object($issue)) {

                if ($issue->getNumber())
                    $data = array('success' => true, 'message' => "Your report has been submitted");

            } else {
                $data = array('success' => false, 'message' => "Something went wrong");
            }

        } else {
            $data = array('success' => false, 'message' => "You must provide a title");
        }

        echo(json_encode($data));
    }

}
