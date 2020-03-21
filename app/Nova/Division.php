<?php

namespace App\Nova;

use App\Nova\Actions\SetDefaultSettings;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\Date;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Panel;

class Division extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \App\Division::class;

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
        'name',
    ];

    public static $with = [
        'handle'
    ];

    /**
     * Get the fields displayed by the resource.
     *
     * @param Request $request
     * @return array
     */
    public function fields(Request $request)
    {
        return [
            ID::make()->sortable(),

            Text::make('Name')->sortable()
                ->help('Should match Clan AOD division name'),

            Text::make('Abbreviation')
                ->help('Should match Clan AOD abbreviation'),

            Number::make('officer_role_id')
                ->help('Id assigned by the AOD Forums for the division officers group'),

            BelongsTo::make('Handle')
                ->help("If a handle doesn't exist, create it first"),

            Date::make('Created At')->sortable(),

            Date::make('Updated At')->sortable(),

            new Panel('Extra stuff', function () {
                return [
                    Text::make('description')->rules(['required'])->hideFromIndex(),
                    Boolean::make('active')->sortable(),
//                    Code::make('settings')->json()->hideWhenUpdating(),
                ];
            }),

        ];
    }

    /**
     * Get the cards available for the request.
     *
     * @param Request $request
     * @return array
     */
    public function cards(Request $request)
    {
        return [];
    }

    /**
     * Get the filters available for the resource.
     *
     * @param Request $request
     * @return array
     */
    public function filters(Request $request)
    {
        return [];
    }

    /**
     * Get the lenses available for the resource.
     *
     * @param Request $request
     * @return array
     */
    public function lenses(Request $request)
    {
        return [];
    }

    /**
     * Get the actions available for the resource.
     *
     * @param Request $request
     * @return array
     */
    public function actions(Request $request)
    {
        return [
            new SetDefaultSettings(),
        ];
    }
}
