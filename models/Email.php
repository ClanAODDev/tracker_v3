<?php

class Email {

  public $to;
  public $subject;
  public $message;
  public $headers;

  static $bcc = "admin@aodwebhost.site.nfoservers.com";
  static $from = "AOD Division Tracker <admin@aodwebhost.site.nfoservers.com>";

  public function send() {
    if (!empty($this->to)) 
      mail($this->to, $this->subject, $this->message, $this->headers);
  }

  public static function validate(User $user) {
    $email = new self();
    $email->headers = "From: " . self::$from . "\r\n";
    $email->headers .= "MIME-Version: 1.0\r\n";
    $email->headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";

    $email->to = $user->email;
    $email->subject = "AOD Division Tracker - Email verification";
    $email->message .= "<h1><strong>{$user->username}</strong>,</h1>";
    $email->message .= "<p>This email was used by someone with the IP {$_SERVER['REMOTE_ADDR']} to create an account on the AOD Division Tracker. Please verify that it was you by clicking the link provided below, or copy-paste the URL into your browser's address bar.</p>";
    $email->message .= "<p>http://aodwebhost.site.nfoservers.com/tracker/authenticate?id={$user->validation}\r\n\r\n</p>";
    $email->message .= "<p><small>If you believe you have received this email in error, or the account was not created by you, please let us know by sending an email to admin@aodwebhost.site.nfoservers.com</small></p>";
        $email->message .= "<p><small>PLEASE DO NOT REPLY TO THIS E-MAIL</small></p>";
    $email->send();
  }

}