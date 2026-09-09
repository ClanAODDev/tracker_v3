<?php

use App\Enums\Rank;
use App\Http\Controllers\AwardController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('clan/ranks', function () {
    $groups = ['admin' => 'Clan Admin', 'officer' => 'Officer', 'enlisted' => 'Enlisted'];

    $sections = collect($groups)->map(fn (string $label, string $group) => [
        'label' => $label,
        'ranks' => collect(array_reverse(Rank::cases()))
            ->filter(fn (Rank $rank) => $rank->getGroup() === $group)
            ->map(fn (Rank $rank) => [
                'abbr'   => $rank->getAbbreviation(),
                'name'   => $rank->getLabel(),
                'duties' => $rank->getDuties(),
                'tier'   => $rank->getTier(),
                'color'  => $rank->getColorHex(),
            ])->values(),
    ])->values();

    return Inertia::render('clan/ranks', ['sections' => $sections]);
})->name('clan.ranks');

Route::get('clan/code-of-conduct', fn () => Inertia::render('clan/code-of-conduct', [
    'rules' => [
        'Strive to conduct ourselves in an appropriate manner',
        'Offer help to anyone who seeks it, if it is possible',
        'Project a positive image of yourself and the AOD clan to others',
        'Put the needs of the clan first, above personal goals',
        'Support members of AOD',
        'Attempt to resolve personal differences directly with the concerned individual(s)',
        'Remind other AOD members of expected conduct in a tactful, non-threatening way, and if possible, in private',
        'Promote fellowship within the game community',
        'Do not condemn or humiliate other clan members',
        'Win and lose games honorably — show sportsmanship',
        'Maintain AOD loyalty',
        'No comments about politics, religion, skin color, sexual preferences, or sexual conduct. We are here to play games.',
    ],
]))->name('clan.code-of-conduct');

Route::controller(AwardController::class)->prefix('clan/awards')->name('awards.')->group(function () {
    Route::get('/', 'index')->name('index');
    Route::get('tiered/{slug}', 'tiered')->name('tiered');
    Route::get('{award}', 'show')->name('show');
    Route::post('{award}', 'storeRecommendation')->name('store-recommendation');
});
