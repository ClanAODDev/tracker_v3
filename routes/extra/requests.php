<?php

use App\Models\Division;
use App\Models\MemberRequest;

Route::get('requests-count.png', function () {
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Cache-Control: post-check=0, pre-check=0', false);
    header('Pragma: no-cache');
    header('Content-Type: image/png');
    date_default_timezone_set('America/New_York');

    $tinyfont = public_path('fonts/copy0855.ttf');
    $tinyboldfont = public_path('fonts/copy0866.ttf');
    $bigfont = public_path('fonts/din-black.otf');

    $context = [
        'ssl' => [
            'verify_peer'      => false,
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

    $requestsCount = MemberRequest::pending()->pastGracePeriod();

    if (request()->has('division')) {
        $division = Division::whereAbbreviation(request('division'))->firstOrFail();
        $requestsCount = $requestsCount->where('division_id', $division->id);
    }

    $errors = MemberRequest::errors()->count();

    // calculate X for number of requests
    $dimensionsRequests = imagettfbbox(20, 0, $bigfont, $requestsCount->count());
    $textWidthRequests = abs($dimensionsRequests[4] - $dimensionsRequests[0]);
    $xRequests = imagesx($im)                       - $textWidthRequests;

    imagettftext($im, 20, 0, $xRequests - 10, 25, $orange, $bigfont, $requestsCount->count());

    if ($errors > 0) {
        imagettftext($im, 6, 0, $xRequests - 50, 20, $red, $tinyfont, "({$errors} ERR)");
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
            'verify_peer'      => false,
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

    $ticketsCount = \App\Models\Ticket::open()->count();

    // calculate X for number of tickets
    $dimensionsTickets = imagettfbbox(20, 0, $bigfont, $ticketsCount);
    $textWidthTickets = abs($dimensionsTickets[4] - $dimensionsTickets[0]);
    $xTickets = imagesx($im)                      - $textWidthTickets;

    imagettftext($im, 20, 0, $xTickets - 10, 25, $orange, $bigfont, $ticketsCount);

    imagepng($im);
    imagedestroy($im);
});
