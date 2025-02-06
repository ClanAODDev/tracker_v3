<?php

namespace App\Filament\Mod\Resources;

use App\Filament\Mod\Resources\PlatoonResource\Pages;
use App\Filament\Mod\Resources\PlatoonResource\RelationManagers\SquadsRelationManager;
use App\Models\Platoon;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class PlatoonResource extends Resource
{
    protected static ?string $model = Platoon::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Division';

    public static function canDeleteAny(): bool
    {
        return auth()->user()->isRole(['admin', 'sr_ldr']) || auth()->user()->isDivisionLeader();
    }

    public static function canEdit(Model $record): bool
    {
        if (auth()->user()->isRole(['admin', 'sr_ldr']) || auth()->user()->isDivisionLeader()) {
            return true;
        }

        if (auth()->user()->member->platoon_id == $record->id && auth()->user()->isPlatoonLeader()) {
            return true;
        }

        return false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('order')
                    ->label('Sort order')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('logo')
                    ->placeholder('https://')
                    ->maxLength(255)
                    ->default(null),
                Select::make('leader_id')
                    ->relationship('leader', 'name', function ($query) {
                        $query->where('division_id', auth()->user()->member->division_id);
                    })
                    ->label('Leader (from current division)')
                    ->searchable()
                    ->helperText('Leave blank if position not yet assigned')
                    ->nullable(),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('division.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('leader.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])->modifyQueryUsing(function ($query) {
                $query->where('division_id', auth()->user()->member->division_id);
            })
            ->filters([
                //
            ])

            ->actions([
                Tables\Actions\EditAction::make(),
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
            SquadsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPlatoons::route('/'),
            'create' => Pages\CreatePlatoon::route('/create'),
            'edit' => Pages\EditPlatoon::route('/{record}/edit'),
        ];
    }
}
