<?php

Route::get('requests-count.png', function () {
    header('Content-Type: image/png');
    date_default_timezone_set('America/New_York');

    $tinyfont = public_path('fonts/copy0855.ttf');
    $tinyboldfont = public_path('fonts/copy0866.ttf');
    $bigfont = public_path('fonts/din-black.otf');
    $im = imagecreatefromjpeg(asset('images/dynamic-images/bgs/pending-requests.jpg'));
    $orange = imagecolorallocate($im, 255, 108, 0);
    $red = imagecolorallocate($im, 153,26,34);
    $requestsCount = \App\MemberRequest::pending()->count();
    $errors = \App\MemberRequest::errors()->count();
    $dimensions = imagettfbbox(20, 0, $bigfont, $requestsCount);
    $textWidth = abs($dimensions[4] - $dimensions[0]);
    $x = imagesx($im) - $textWidth;
#991A22
    imagettftext($im, 20, 0, $x - 10, 25, $orange, $bigfont, $requestsCount);
    imagettftext($im, 6, 0, $x - 50, 20, $red, $tinyfont, "({$errors} ERR)");
    imagepng($im);
    imagedestroy($im);
});
