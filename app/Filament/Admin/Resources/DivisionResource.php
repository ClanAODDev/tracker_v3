<?php

namespace App\Filament\Admin\Resources;

use App\Enums\Position;
use App\Filament\Admin\Resources\DivisionResource\Pages\CreateDivision;
use App\Filament\Admin\Resources\DivisionResource\Pages\EditDivision;
use App\Filament\Admin\Resources\DivisionResource\Pages\ListDivisions;
use App\Models\Division;
use App\Models\Member;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use ValentinMorice\FilamentJsonColumn\JsonColumn;

class DivisionResource extends Resource
{
    protected static ?string $model = Division::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static string|\UnitEnum|null $navigationGroup = 'Division';

    private static string $becy = 'https://jarlpenguin.github.io/BeCyIconGrabberPortable/';

    private static string $forms = 'https://www.clanaod.net/forums/forms.php';

    private static string $usergroup_mod = 'https://www.clanaod.net/forums/admincp/usergroup.php?do=modify';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Tracker Details')
                    ->columnSpanFull()
                    ->schema([
                        FileUpload::make('logo')
                            ->label('Logo (48x48)')
                            ->hint(str(sprintf('[Icon Extraction Tool >](%s)',
                                self::$becy
                            ))->inlineMarkdown()->toHtmlString())
                            ->required()
                            ->alignCenter()
                            ->avatar()
                            ->directory('logos'),

                        Fieldset::make()->schema([
                            TextInput::make('name')
                                ->required()
                                ->maxLength(255),

                            Select::make('handle_id')
                                ->label('Game Handle')
                                ->relationship('handle', 'label'),

                            TextInput::make('abbreviation')
                                ->helperText('Should match abbreviation used on forums')
                                ->maxLength(3)
                                ->required()
                                ->maxLength(255),
                        ])->columns(3),

                        TextInput::make('description')
                            ->default('Another AOD Division')
                            ->hint('Deprecated and soon to be removed')
                            ->maxLength(255),

                        JsonColumn::make('settings')
                            ->hiddenOn('create'),
                    ]),

                Section::make('Leadership Management')
                    ->columnSpanFull()
                    ->description('Manage division leaders')
                    ->hiddenOn('create')
                    ->schema([
                        Section::make()->schema([
                            TextInput::make('current_co_name')
                                ->label('Current CO')
                                ->disabled()
                                ->dehydrated(false)
                                ->afterStateHydrated(function ($state, Set $set) use ($schema) {
                                    $coName = Member::query()
                                        ->where('division_id', $schema->getRecord()?->id)
                                        ->where('position', Position::COMMANDING_OFFICER)
                                        ->value('name');
                                    $set('current_co_name', $coName);
                                }),

                            Select::make('new_co')
                                ->label('New CO')
                                ->searchable()
                                ->options(fn () => Member::where('division_id', $schema->getRecord()?->id)
                                    ->pluck('name', 'id')),
                        ])->columns(),

                        Repeater::make('executive_officers')
                            ->label('Executive Officers')
                            ->schema([
                                Select::make('xo')
                                    ->label('Executive Officer')
                                    ->searchable()
                                    ->options(fn () => Member::where('division_id', $schema->getRecord()?->id)
                                        ->pluck('name', 'id')),
                            ])
                            ->minItems(0)
                            ->maxItems(3)
                            ->addActionLabel('Add XO')
                            ->afterStateHydrated(function ($state, Set $set) use ($schema) {
                                if (! empty($state)) {
                                    return;
                                }

                                $rows = Member::where('division_id', $schema->getRecord()->id)
                                    ->where('position', Position::EXECUTIVE_OFFICER)
                                    ->pluck('id')
                                    ->map(fn ($id) => ['xo' => $id])
                                    ->toArray();

                                $set('executive_officers', $rows);
                            }),

                    ]),

                Section::make('Website')
                    ->columnSpanFull()
                    ->hiddenOn('create')
                    ->schema([
                        Section::make('Website')
                            ->description('Divisional website settings')
                            ->schema([
                                MarkdownEditor::make('site_content')
                                    ->helperText('Changes will prompt an admin review before being published')
                                    ->columnSpanFull(),
                            ])->collapsible()->collapsed(),
                    ]),

                Section::make('Forum Details')
                    ->columnSpanFull()
                    ->description('Division must already have been created in the forums in order to populate these values')
                    ->schema([
                        TextInput::make('officer_role_id')
                            ->hint(str(sprintf(
                                '[View Forum Usergroups](%s)',
                                self::$usergroup_mod
                            ))->inlineMarkdown()->toHtmlString())
                            ->numeric()
                            ->required(),
                        TextInput::make('forum_app_id')
                            ->label('Application form id')
                            ->hint(str(sprintf(
                                '[View Division Forms](%s)',
                                self::$forms
                            ))->inlineMarkdown()->toHtmlString())
                            ->required()
                            ->numeric(),
                    ])->columns(),

                Section::make('Extra settings')
                    ->columnSpanFull()
                    ->schema([
                        DateTimePicker::make('shutdown_at'),
                        Toggle::make('show_on_site')
                            ->label('Show on site')
                            ->hint('Toggle on if division should be visible on the website')
                            ->default(true),
                        Toggle::make('active')
                            ->label('Division Enabled')
                            ->hint('Disabled divisions are not listed on the tracker or website')
                            ->default(true),
                    ])->columns(3),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('logo'),
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('slug')
                    ->searchable(),
                TextColumn::make('abbreviation')
                    ->searchable()
                    ->badge(),
                IconColumn::make('active')
                    ->boolean(),
            ])
            ->filters([
                Filter::make('is_active')
                    ->query(fn (Builder $query): Builder => $query->where('active', true))
                    ->label('Hide inactive')
                    ->default(),

                TrashedFilter::make(),
            ])
            ->filtersLayout(FiltersLayout::AboveContentCollapsible)
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDivisions::route('/'),
            'create' => CreateDivision::route('/create'),
            'edit' => EditDivision::route('/{record}/edit'),
        ];
    }
}
