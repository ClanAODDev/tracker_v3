<?php

Route::get('requests-count.png', function () {
    header('Content-Type: image/png');
    date_default_timezone_set('America/New_York');

    $im = imagecreatetruecolor(960, 330);
    $orange = imagecolorallocate($im, 255, 108, 0);
    $im = imagecreatefromjpeg(asset('images/pending-requests.jpg'));

    $tinyfont = public_path('fonts/copy0855.ttf');
    $tinyboldfont = public_path('fonts/copy0866.ttf');
    $bigfont = public_path('fonts/din-black.otf');

    $requests = \App\MemberRequest::pending();

    imagettftext($im, 30, 0, 230, 40, $orange, $bigfont, $requests->count()+34);

    imagepng($im);
    imagedestroy($im);
});