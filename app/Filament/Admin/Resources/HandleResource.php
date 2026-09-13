<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\HandleResource\Pages\CreateHandle;
use App\Filament\Admin\Resources\HandleResource\Pages\EditHandle;
use App\Filament\Admin\Resources\HandleResource\Pages\ListHandles;
use App\Models\Handle;
use Closure;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class HandleResource extends Resource
{
    protected static ?string $model = Handle::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-identification';

    protected static string|\UnitEnum|null $navigationGroup = 'Configuration';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Handle type')
                    ->description('The game or platform this handle belongs to.')
                    ->schema([
                        TextInput::make('label')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('type')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('url')
                            ->label('Profile URL prefix')
                            ->url()
                            ->prefixIcon('heroicon-o-link')
                            ->placeholder('https://steamcommunity.com/profiles/')
                            ->columnSpanFull(),
                        Textarea::make('comments')
                            ->maxLength(255)
                            ->rows(2)
                            ->columnSpanFull()
                            ->default(null),
                    ])
                    ->columns(2),

                Section::make('Validation')
                    ->description('Optionally enforce a format for values members enter for this handle type.')
                    ->schema([
                        Select::make('regex_preset')
                            ->label('Quick pattern')
                            ->placeholder('Choose a common format to fill in below...')
                            ->options(collect(self::regexPresets())->map(fn (array $preset) => $preset['label'])->all())
                            ->live()
                            ->dehydrated(false)
                            ->afterStateUpdated(function (?string $state, Set $set): void {
                                $preset = self::regexPresets()[$state] ?? null;

                                if (! $preset) {
                                    return;
                                }

                                $set('regex', $preset['regex']);
                                $set('regex_hint', $preset['hint']);
                            })
                            ->columnSpanFull(),
                        TextInput::make('regex')
                            ->label('Validation regex')
                            ->helperText('Optional PCRE pattern a handle value must match, e.g. /^[0-9]+$/ for a numeric-only Steam ID. Leave blank to allow any format.')
                            ->rule(static fn (): Closure => static function (string $attribute, $value, Closure $fail): void {
                                if ($value !== null && $value !== '' && @preg_match($value, '') === false) {
                                    $fail('Enter a valid PCRE pattern, e.g. /^[0-9]+$/.');
                                }
                            })
                            ->maxLength(255),
                        TextInput::make('regex_hint')
                            ->label('Format hint')
                            ->helperText('Shown to members when their value does not match the pattern above, e.g. "Steam ID must be numeric".')
                            ->maxLength(255),
                    ])
                    ->columns(2),
            ]);
    }

    /**
     * @return array<string, array{label: string, regex: string, hint: string}>
     */
    private static function regexPresets(): array
    {
        return [
            'steam' => [
                'label' => 'Steam ID (numeric)',
                'regex' => '/^[0-9]+$/',
                'hint'  => 'Steam ID must be numeric.',
            ],
            'discord' => [
                'label' => 'Discord username',
                'regex' => '/^[a-z0-9_.]{2,32}$/',
                'hint'  => 'Discord usernames are lowercase letters, numbers, underscores, and periods (2-32 characters).',
            ],
            'xbox' => [
                'label' => 'Xbox Gamertag',
                'regex' => '/^[A-Za-z0-9 ]{1,15}$/',
                'hint'  => 'Xbox Gamertags are 1-15 characters (letters, numbers, spaces).',
            ],
            'psn' => [
                'label' => 'PlayStation Network ID',
                'regex' => '/^[A-Za-z][A-Za-z0-9_-]{2,15}$/',
                'hint'  => 'PSN IDs start with a letter and are 3-16 characters (letters, numbers, hyphens, underscores).',
            ],
            'epic' => [
                'label' => 'Epic Games display name',
                'regex' => '/^[A-Za-z0-9]{3,16}$/',
                'hint'  => 'Epic display names are 3-16 alphanumeric characters.',
            ],
            'riot' => [
                'label' => 'Riot ID (Name#TAG)',
                'regex' => '/^.{3,16}#[A-Za-z0-9]{3,5}$/',
                'hint'  => 'Riot ID must be in the form Name#TAG.',
            ],
            'battlenet' => [
                'label' => 'Battle.net BattleTag',
                'regex' => '/^[A-Za-z0-9]{3,12}#[0-9]{4,6}$/',
                'hint'  => 'BattleTag must be in the form Name#12345.',
            ],
            'numeric' => [
                'label' => 'Numeric only',
                'regex' => '/^[0-9]+$/',
                'hint'  => 'Must be numeric only.',
            ],
            'alnum' => [
                'label' => 'Letters and numbers only',
                'regex' => '/^[A-Za-z0-9]+$/',
                'hint'  => 'Must be letters and numbers only, no spaces or symbols.',
            ],
        ];
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('label')
                    ->searchable(),
                TextColumn::make('regex')
                    ->label('Validation regex')
                    ->placeholder('None')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('comments')
                    ->searchable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListHandles::route('/'),
            'create' => CreateHandle::route('/create'),
            'edit'   => EditHandle::route('/{record}/edit'),
        ];
    }
}
