<?php

return [
    'driver' => 'smtp',
    'host' => 'smtp.mailtrap.io',
    'port' => 2525,
    'from' => [
        'address' => 'tracker@clanaod.net',
        'name' => 'Tracker',
    ],
    'username' => '05ed9b2fe5a6f7',
    'password' => '71ab3211ca0b97',
    'sendmail' => '/usr/sbin/sendmail -bs',
    'pretend' => false,
];
