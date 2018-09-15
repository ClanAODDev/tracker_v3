<?php

Route::get('requests-count.png', function () {
    header('Content-Type: image/png');
    date_default_timezone_set('America/New_York');

    $tinyfont = public_path('fonts/copy0855.ttf');
    $tinyboldfont = public_path('fonts/copy0866.ttf');
    $bigfont = public_path('fonts/din-black.otf');
    $im = imagecreatetruecolor(960, 330);
    $orange = imagecolorallocate($im, 255, 108, 0);
    $im = imagecreatefromjpeg(asset('images/dynamic-images/bgs/pending-requests.jpg'));
    $requestsCount = \App\MemberRequest::pending()->count();
    $dimensions = imagettfbbox(20, 0, $bigfont, $requestsCount);
    $textWidth = abs($dimensions[4] - $dimensions[0]);
    $x = imagesx($im) - $textWidth;

    imagettftext($im, 20, 0, $x-10, 25, $orange, $bigfont, $requestsCount);
    imagepng($im);
    imagedestroy($im);
});
