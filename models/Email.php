<?php

class Email {

  public $to;
  public $subject;
  public $message;
  public $headers;

  static $bcc = "admin@aodwebhost.site.nfoservers.com";
  static $from = "AOD Division Tracker <admin@aodwebhost.site.nfoservers.com";

  public function send() {
    if (!empty($this->to)) 
      mail($this->to, $this->subject, $this->message, $this->headers);
  }

  public static function validate(User $user) {
    $email = new self();
    $email->headers = "From: " . self::$from . "\r\n";
    $email->headers .= "Bcc: " . self::$bcc . "\r\n";
    $email->to = $user->email;
    $email->subject = "Account created";
    $email->message = "PLEASE DO NOT REPLY TO THIS E-MAIL\r\n\n";
    $email->message .= "{$user->username},\r\n";
    $email->message .= "This email was used to create an account on the AOD Division Tracker. Please verify that it was you by clicking the link provided below, or copy-paste the URL into your browser's address bar.\r\n";
    $email->message .= "http://aodwebhost.site.nfoservers.com/tracker/validate?id={$user->validation}\r\n\r\n";
    $email->message .= "If you believe you have received this email in error, or the account was not created by you, please let us know by sending an email to admin@aodwebhost.site.nfoservers.com";
    $email->send();
  }

}