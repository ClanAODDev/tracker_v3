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
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
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
            ->components([
                Textarea::make('label')
                    ->required()
                    ->columnSpanFull(),
                TextInput::make('type')
                    ->required()
                    ->maxLength(255),
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
                Textarea::make('comments')
                    ->maxLength(255)
                    ->columnSpanFull()
                    ->default(null),
                TextInput::make('url')
                    ->columnSpanFull(),
            ]);
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
