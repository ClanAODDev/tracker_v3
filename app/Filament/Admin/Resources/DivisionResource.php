<?php

namespace App\Filament\Admin\Resources;

use App\Enums\Position;
use App\Filament\Admin\Resources\DivisionResource\Pages;
use App\Models\Division;
use App\Models\Member;
use Filament\Forms;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Mansoor\FilamentVersionable\Table\RevisionsAction;
use ValentinMorice\FilamentJsonColumn\JsonColumn;

class DivisionResource extends Resource
{
    protected static ?string $model = Division::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Division';

    private static string $becy = 'https://jarlpenguin.github.io/BeCyIconGrabberPortable/';

    private static string $forms = 'https://www.clanaod.net/forums/forms.php';

    private static string $usergroup_mod = 'https://www.clanaod.net/forums/admincp/usergroup.php?do=modify';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Tracker Details')
                    ->schema([
                        Forms\Components\FileUpload::make('logo')
                            ->label('Logo (48x48)')
                            ->hint(str(sprintf('[Icon Extraction Tool >](%s)',
                                self::$becy
                            ))->inlineMarkdown()->toHtmlString())
                            ->required()
                            ->alignCenter()
                            ->avatar()
                            ->directory('logos'),

                        Forms\Components\Fieldset::make()->schema([
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

                Forms\Components\Section::make('Leadership Management')
                    ->description('Manage division leaders')
                    ->schema([
                        Forms\Components\Section::make()->schema([
                            TextInput::make('current_co_name')
                                ->label('Current CO')
                                ->disabled()
                                ->dehydrated(false)
                                ->afterStateHydrated(function ($state, Set $set) use ($form) {
                                    $coName = \App\Models\Member::query()
                                        ->where('division_id', $form->getRecord()->id)
                                        ->where('position', \App\Enums\Position::COMMANDING_OFFICER)
                                        ->value('name');
                                    $set('current_co_name', $coName);
                                }),

                            Select::make('new_co')
                                ->label('New CO')
                                ->searchable()
                                ->options(fn() => Member::where('division_id', $form->getRecord()->id)
                                    ->pluck('name', 'id')),
                        ])->columns(),

                        Repeater::make('executive_officers')
                            ->label('Executive Officers')
                            ->schema([
                                Select::make('xo')
                                    ->label('Executive Officer')
                                    ->searchable()
                                    ->options(fn() => Member::where('division_id', $form->getRecord()->id)
                                        ->pluck('name', 'id')),
                            ])
                            ->minItems(0)
                            ->maxItems(3)
                            ->addActionLabel('Add XO')
                            ->afterStateHydrated(function ($state, Set $set) use ($form) {
                                if (!empty($state)) {
                                    return;
                                }

                                $rows = Member::where('division_id', $form->getRecord()->id)
                                    ->where('position', Position::EXECUTIVE_OFFICER)
                                    ->pluck('id')
                                    ->map(fn($id) => ['xo' => $id])
                                    ->toArray();

                                $set('executive_officers', $rows);
                            }),

                    ]),

                Section::make('Website')
                    ->hiddenOn('create')
                    ->schema([
                        Forms\Components\Section::make('Website')
                            ->description('Divisional website settings')
                            ->schema([
                                Forms\Components\MarkdownEditor::make('site_content')
                                    ->helperText('Changes will prompt an admin review before being published')
                                    ->columnSpanFull(),
                            ])->collapsible()->collapsed(),
                    ]),

                Section::make('Forum Details')
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

                Section::make('Extra settings')->schema([

                    Forms\Components\DateTimePicker::make('shutdown_at'),

                    Toggle::make('show_on_site')
                        ->label('Show on site')
                        ->hint('Toggle on if division should be visible on the website')
                        ->default(true),

                    Toggle::make('active')
                        ->label('Division Enabled')
                        ->hint('Disabled divisions are not listed on the tracker or website')
                        ->default(true),
                ]),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('logo'),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('slug')
                    ->searchable(),
                Tables\Columns\TextColumn::make('abbreviation')
                    ->searchable()
                    ->badge(),
                Tables\Columns\IconColumn::make('active')
                    ->boolean(),
            ])
            ->filters([
                Filter::make('is_active')
                    ->query(fn(Builder $query): Builder => $query->where('active', true))
                    ->label('Hide inactive')
                    ->default(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                RevisionsAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListDivisions::route('/'),
            'create' => Pages\CreateDivision::route('/create'),
            'edit' => Pages\EditDivision::route('/{record}/edit'),
            'revisions' => Pages\DivisionRevisions::route('/{record}/revisions'),
        ];
    }
}
