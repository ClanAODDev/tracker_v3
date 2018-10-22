<?php

Route::get('requests-count.png', function () {
    header('Content-Type: image/png');
    date_default_timezone_set('America/New_York');

    $tinyfont = public_path('fonts/copy0855.ttf');
    $tinyboldfont = public_path('fonts/copy0866.ttf');
    $bigfont = public_path('fonts/din-black.otf');
    $im = imagecreatefromjpeg(asset('images/dynamic-images/bgs/pending-requests.jpg'));
    $orange = imagecolorallocate($im, 255, 108, 0);
    $red = imagecolorallocate($im, 153, 26, 34);
    $requestsCount = \App\MemberRequest::pending()->count();
    $errors = \App\MemberRequest::errors()->count();
    $dimensions = imagettfbbox(20, 0, $bigfont, $requestsCount);
    $textWidth = abs($dimensions[4] - $dimensions[0]);
    $x = imagesx($im) - $textWidth;

    imagettftext($im, 20, 0, $x - 10, 25, $orange, $bigfont, $requestsCount);

    if ($errors > 0) {
        imagettftext($im, 6, 0, $x - 50, 20, $red, $tinyfont, "({$errors} ERR)");
    }

    imagepng($im);
    imagedestroy($im);
});


Route::middleware('auth')->group(function () {
    Route::get('vegas', function () {
        dd(\App\VegasAttendee::all());
        $optedIn = DB::table('opt_in')
            ->where('member_id', auth()->user()->member->clan_id)
            ->exists();

        return view('pages.vegas-opt-in', compact('optedIn'));
    })->middleware('auth')->name('vegas-survey');

    Route::post('vegas/opt-in', function () {
        $user = auth()->user()->member;

        DB::insert('insert into opt_in (member_id, created_at, updated_at) values (?, ?, ?)', [
            $user->clan_id,
            now(),
            now()
        ]);

        return redirect(route('vegas-survey'));
    })->middleware('auth');

    Route::delete('vegas/opt-out', function () {
        $user = auth()->user()->member;
        DB::delete('delete from opt_in where member_id = ? LIMIT 1', [$user->clan_id]);

        return redirect(route('vegas-survey'));
    });
});
