<?php

namespace App\Nova;

use App\Nova\Actions\PrunePartTimers;
use App\Nova\Actions\SetDefaultSettings;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\Code;
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
    public static $model = \App\Models\Division::class;

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
        'handle',
    ];

    public static $group = 'Admin';

    /**
     * Get the fields displayed by the resource.
     *
     * @return array
     */
    public function fields(Request $request)
    {
        return [
            ID::make()->sortable(),

            Text::make('Name')->sortable()
                ->help("Should match Clan AOD division name. Do not change unless you know what you're doing!!"),

            Text::make('Slug')->sortable()
                ->help("Slug version of name [used for website]. Do not change unless you know what you're doing!!"),

            Text::make('Abbreviation')
                ->help('Should match Clan AOD abbreviation. LOWER-CASE'),

            Number::make('officer_role_id')
                ->help('Id assigned by the AOD Forums for the division officers group'),

            BelongsTo::make('Handle')
                ->help("If a handle doesn't exist, create it first"),

            Number::make('forum_app_id')
                ->help('Numerical id of division application form id'),

            Code::make('Settings')
                ->json(),

            Date::make('Created At')->sortable(),

            Date::make('Updated At')->sortable(),

            Date::make('Shutdown At'),

            new Panel('Extra stuff', fn () => [
                Text::make('description')->rules(['required'])->hideFromIndex(),
                Boolean::make('active')->sortable()->rules(
                    'required',
                    function ($attribute, $value, $fail) {
                        if (false === $value && $this->members->count()) {
                            return $fail('Division still has members assigned and cannot be disabled.');
                        }
                    }
                ),
            ]),
        ];
    }

    /**
     * Get the cards available for the request.
     *
     * @return array
     */
    public function cards(Request $request)
    {
        return [];
    }

    /**
     * Get the filters available for the resource.
     *
     * @return array
     */
    public function filters(Request $request)
    {
        return [];
    }

    /**
     * Get the lenses available for the resource.
     *
     * @return array
     */
    public function lenses(Request $request)
    {
        return [];
    }

    /**
     * Get the actions available for the resource.
     *
     * @return array
     */
    public function actions(Request $request)
    {
        return [
            new SetDefaultSettings(),
            new PrunePartTimers(),
        ];
    }
}
