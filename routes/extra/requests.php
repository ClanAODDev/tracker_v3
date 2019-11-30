<?php

use Illuminate\Support\Facades\Mail;

Route::get('requests-count.png', function () {
    header('Content-Type: image/png');
    date_default_timezone_set('America/New_York');

    $tinyfont = public_path('fonts/copy0855.ttf');
    $tinyboldfont = public_path('fonts/copy0866.ttf');
    $bigfont = public_path('fonts/din-black.otf');

    $context = [
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false,
        ],
    ];

    $response = file_get_contents(
        asset('images/dynamic-images/bgs/pending-requests.jpg'),
        false,
        stream_context_create($context)
    );

    $im = imagecreatefromstring($response);
    $orange = imagecolorallocate($im, 255, 108, 0);
    $red = imagecolorallocate($im, 153, 26, 34);

    $requestsCount = \App\MemberRequest::pending()->pastGracePeriod()->count();
    $errors = \App\MemberRequest::errors()->count();

    // calculate X for number of requests
    $dimensionsRequests = imagettfbbox(20, 0, $bigfont, $requestsCount);
    $textWidthRequests = abs($dimensionsRequests[4] - $dimensionsRequests[0]);
    $xRequests = imagesx($im) - $textWidthRequests;

    imagettftext($im, 20, 0, $xRequests - 10, 25, $orange, $bigfont, $requestsCount);

    if ($errors > 0) {
        imagettftext($im, 6, 0, $x - 50, 20, $red, $tinyfont, "({$errors} ERR)");
    }

    imagepng($im);
    imagedestroy($im);
});

Route::get('tickets-count.png', function () {
    header('Content-Type: image/png');
    date_default_timezone_set('America/New_York');

    $tinyfont = public_path('fonts/copy0855.ttf');
    $tinyboldfont = public_path('fonts/copy0866.ttf');
    $bigfont = public_path('fonts/din-black.otf');

    $context = [
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false,
        ],
    ];

    $response = file_get_contents(
        asset('images/dynamic-images/bgs/pending-tickets.jpg'),
        false,
        stream_context_create($context)
    );

    $im = imagecreatefromstring($response);
    $orange = imagecolorallocate($im, 255, 108, 0);

    $ticketsCount = \App\Ticket::open()->count();

    // calculate X for number of tickets
    $dimensionsTickets = imagettfbbox(20, 0, $bigfont, $ticketsCount);
    $textWidthTickets = abs($dimensionsTickets[4] - $dimensionsTickets[0]);
    $xTickets = imagesx($im) - $textWidthTickets;
    
    imagettftext($im, 20, 0, $xTickets - 10, 25, $orange, $bigfont, $ticketsCount);

    imagepng($im);
    imagedestroy($im);
});

