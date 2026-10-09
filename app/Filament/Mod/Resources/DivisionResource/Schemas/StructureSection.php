<?php

namespace App\Filament\Mod\Resources\DivisionResource\Schemas;

use App\Enums\Rank;
use App\Enums\UnitLeaderPower;
use App\Filament\Mod\Resources\DivisionResource;
use App\Models\Division;
use App\Models\Unit;
use App\Services\Units\UnitAssignment;
use Filament\Actions\Action;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Illuminate\Support\HtmlString;

class StructureSection
{
    public static function make(): Section
    {
        return Section::make('Structure')
            ->description('Name each level of your division and see what its leaders can do.')
            ->collapsible()
            ->columns(2)
            ->schema([
                Repeater::make('unitLevels')
                    ->label('Levels')
                    ->relationship('unitLevels')
                    ->orderColumn('depth')
                    ->reorderable(false)
                    ->minItems(fn (Division $record) => (int) $record->units()->max('depth'))
                    ->validationMessages([
                        'min' => fn (Division $record) => sprintf(
                            'This division still has units at level %1$d, so it needs at least %1$d levels. Archive or move those units first.',
                            (int) $record->units()->max('depth'),
                        ),
                    ])
                    ->maxItems(Division::MAX_UNIT_LEVELS)
                    ->live()
                    ->schema([
                        TextInput::make('label')->required()->maxLength(50),
                        TextInput::make('label_plural')->label('Plural')->required()->maxLength(50),
                        TextInput::make('leader_title')->required()->maxLength(50),
                    ])
                    ->itemLabel(fn (array $state) => $state['label'] ?? null)
                    ->helperText('Levels can only be removed once no units use them. Divisions can have up to four levels, or none at all if members are not organized into units.'),
                Placeholder::make('leader_powers')
                    ->label('What leaders can do')
                    ->content(fn (Get $get, Division $record) => self::leaderPowersPreview($get, $record)),
                Actions::make([
                    Action::make('add_top_level')
                        ->label('Add a level above')
                        ->icon('heroicon-o-arrow-up-circle')
                        ->color('gray')
                        ->visible(fn (Division $record) => $record->unitLevels()->count() >= 1
                            && $record->unitLevels()->count() < Division::MAX_UNIT_LEVELS
                            && auth()->user()->can('create', [Unit::class, $record]))
                        ->modalHeading('Add a level above the existing units')
                        ->modalDescription(fn (Division $record) => sprintf(
                            'Creates a new top level and one unit in it. Every existing top-level %s moves under that unit and each level shifts down by one. Unsaved changes on this page are discarded.',
                            strtolower($record->unitLevel(1)?->label ?? 'unit'),
                        ))
                        ->schema([
                            TextInput::make('label')->label('Level name')->placeholder('Company')->required()->maxLength(50),
                            TextInput::make('label_plural')->label('Plural')->placeholder('Companies')->required()->maxLength(50),
                            TextInput::make('leader_title')->placeholder('Company Commander')->required()->maxLength(50),
                            TextInput::make('unit_name')->label('Name of the first unit')->required()->maxLength(255),
                        ])
                        ->action(function (array $data, Division $record) {
                            app(UnitAssignment::class)->insertTopLevel(
                                $record,
                                ['label' => $data['label'], 'label_plural' => $data['label_plural'], 'leader_title' => $data['leader_title']],
                                ['name'  => $data['unit_name']],
                            );

                            Notification::make()->success()->title("{$data['label']} level added")->send();

                            return redirect(DivisionResource::getUrl('edit', ['record' => $record]));
                        }),
                ])->key('structure_actions')->columnSpanFull(),
            ]);
    }

    private static function leaderPowersPreview(Get $get, Division $division): HtmlString
    {
        $levels = collect(array_values($get('unitLevels') ?? []))
            ->map(fn (array $level, int $index) => [...$level, 'depth' => $index + 1]);

        if ($levels->isEmpty()) {
            return new HtmlString('<p style="font-size: 0.875rem; opacity: 0.7;">This division has no unit levels. Members belong directly to the division.</p>');
        }

        $limit = Rank::tryFrom((int) $get('settings.max_platoon_leader_rank'))
            ?? Rank::from($division->settings()->get('max_platoon_leader_rank'));

        $preview = $levels->map(fn (array $level) => [
            ...$level,
            'covers' => strtolower($levels->firstWhere('depth', $level['depth'] + 1)['label'] ?? ''),
            'powers' => UnitLeaderPower::forLevel($levels, $level['depth'], $limit),
        ]);

        return new HtmlString(view('filament.forms.components.unit-levels-preview', ['levels' => $preview])->render());
    }
}
