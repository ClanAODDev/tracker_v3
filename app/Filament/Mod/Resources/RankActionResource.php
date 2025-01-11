<?php

namespace App\Filament\Mod\Resources;

use App\Enums\Rank;
use App\Filament\Mod\Resources\RankActionResource\Pages;
use App\Models\RankAction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class RankActionResource extends Resource
{
    protected static ?string $model = RankAction::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-plus';

    protected static ?string $navigationGroup = 'Division';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('member_id')
                    ->relationship('member', 'name', function (Builder $query) {
                        $query->whereHas('division', function (Builder $subQuery) {
                            $subQuery->where('active', true);
                        });
                    })
                    ->searchable()
                    ->required(),
                Forms\Components\Select::make('rank')
                    ->options(Rank::class)
                    ->required(),
                Forms\Components\DateTimePicker::make('created_at')
                    ->default(now())
                    ->required(),
                Forms\Components\DateTimePicker::make('updated_at')
                    ->default(now())
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('member.name'),
                Tables\Columns\TextColumn::make('rank')
                    ->sortable()
                    ->badge(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])->defaultSort('created_at', 'desc')
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRankActions::route('/'),
            'create' => Pages\CreateRankAction::route('/create'),
            'edit' => Pages\EditRankAction::route('/{record}/edit'),
        ];
    }
}
