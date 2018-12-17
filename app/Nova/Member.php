<?php

namespace App\Nova;

use App\Nova\Filters\ByDivision;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Date;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\HasOne;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Panel;

class Member extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = 'App\Member';

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'name';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'clan_id',
        'name',
    ];

    /**
     * @param NovaRequest $request
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function indexQuery(NovaRequest $request, $query)
    {
        return $query->where('division_id', '!=', 0);
    }

    /**
     * Get the fields displayed by the resource.
     *
     * @param  \Illuminate\Http\Request $request
     * @return array
     */
    public function fields(Request $request)
    {
        return [
//            ID::make()->sortable(),

            Number::make('Clan Id')->sortable(),

            Text::make('Name'),

            BelongsTo::make('Rank'),

            BelongsTo::make('Division'),

            HasOne::make('User'),

            Number::make('Recruiter', 'recruiter_id')->hideFromIndex(),

            new Panel('SGT Info', function () {
                return [
                    Date::make('Last Trained', 'last_trained_at')->hideFromIndex(),
                    Number::make('Trained By', 'last_trained_by')->hideFromIndex(),
                    Date::make('XO Since', 'xo_at')->hideFromIndex(),
                    Date::make('CO Since', 'co_at')->hideFromIndex(),
                ];
            }),

            HasMany::make('Notes'),

        ];
    }

    /**
     * Get the cards available for the request.
     *
     * @param  \Illuminate\Http\Request $request
     * @return array
     */
    public function cards(Request $request)
    {
        return [
            (new Metrics\MembersByMonth())->width('full')
        ];
    }

    /**
     * Get the filters available for the resource.
     *
     * @param  \Illuminate\Http\Request $request
     * @return array
     */
    public function filters(Request $request)
    {
        return [
            new ByDivision(),
        ];
    }

    /**
     * Get the lenses available for the resource.
     *
     * @param  \Illuminate\Http\Request $request
     * @return array
     */
    public function lenses(Request $request)
    {
        return [];
    }

    /**
     * Get the actions available for the resource.
     *
     * @param  \Illuminate\Http\Request $request
     * @return array
     */
    public function actions(Request $request)
    {
        return [];
    }
}
