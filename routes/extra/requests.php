<?php

use App\Models\Division;
use App\Models\MemberRequest;
use Illuminate\Support\Facades\Route;

Route::get('requests-count.png', function () {
    date_default_timezone_set('America/New_York');

    $tinyfont = public_path('fonts/copy0855.ttf');
    $tinyboldfont = public_path('fonts/copy0866.ttf');
    $bigfont = public_path('fonts/din-black.otf');

    if (! file_exists($tinyfont) || ! file_exists($bigfont)) {
        return response('Font file missing', 500)
            ->header('Content-Type', 'text/plain');
    }

    $bgPath = public_path('images/dynamic-images/bgs/pending-requests.jpg');
    if (! file_exists($bgPath)) {
        return response('Background image not found', 500)
            ->header('Content-Type', 'text/plain');
    }

    $im = imagecreatefromjpeg($bgPath);
    if (! $im) {
        return response('Failed to load image', 500)
            ->header('Content-Type', 'text/plain');
    }

    $orange = imagecolorallocate($im, 255, 108, 0);
    $red = imagecolorallocate($im, 153, 26, 34);

    $requestsCountQuery = MemberRequest::pending()->pastGracePeriod();
    if (request()->has('division')) {
        $division = Division::whereAbbreviation(request('division'))->firstOrFail();
        $requestsCountQuery->where('division_id', $division->id);
    }

    $requestsCount = $requestsCountQuery->count();
    $errors = MemberRequest::errors()->count();

    $dimensionsRequests = imagettfbbox(20, 0, $bigfont, $requestsCount);
    $textWidthRequests = abs($dimensionsRequests[4] - $dimensionsRequests[0]);
    $xRequests = imagesx($im) - $textWidthRequests;

    imagettftext($im, 20, 0, $xRequests - 10, 25, $orange, $bigfont, $requestsCount);

    if ($errors > 0) {
        $errorText = "({$errors} ERR)";
        $dimensionsError = imagettfbbox(6, 0, $tinyfont, $errorText);
        $textWidthError = abs($dimensionsError[4] - $dimensionsError[0]);
        $xError = $xRequests - $textWidthError - 15;

        imagettftext($im, 6, 0, $xError, 20, $red, $tinyfont, $errorText);
    }

    ob_start();
    imagepng($im);
    $imageData = ob_get_clean();
    imagedestroy($im);

    return response($imageData, 200)
        ->header('Content-Type', 'image/png')
        ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
        ->header('Pragma', 'no-cache');
});
