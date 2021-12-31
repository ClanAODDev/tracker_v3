<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;
use OptimistDigital\MultiselectField\Multiselect;

class TicketType extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \App\Models\TicketType::class;

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

    public static $group = "Admin";

    public static function label()
    {
        return 'Ticket Types';
    }

    /**
     * Get the fields displayed by the resource.
     *
     * @return array
     */
    public function fields(Request $request)
    {
        return [
            ID::make()->sortable(),
            Text::make('Name'),
            Text::make('Slug')->readonly()->onlyOnDetail(),
            Text::make('Description')->help('Provide a general description for the type'),
            Multiselect::make('Role Access')
                ->options([
                    '1' => 'Member',
                    '2' => 'Officer',
                    '3' => 'Junior Leader',
                    '4' => 'Senior Leader',
                    '5' => 'Administrator',
                ])
                ->placeholder('Specify roles') // Placeholder text
                ->help('Provide roles this ticket type should be available to. Leave blank if type should be available to all roles.'),
            Textarea::make('Boilerplate')->help('Pre-populates ticket with basic information, if applicable'),
            Number::make('Display Order')->help('Change the order in which the type is displayed. (Ascending order)'),
            HasMany::make('Ticket'),
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
        return [];
    }
}
