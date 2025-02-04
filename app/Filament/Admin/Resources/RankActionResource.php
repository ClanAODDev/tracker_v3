<?php

namespace App\Filament\Admin\Resources;

use App\Enums\Rank;
use App\Filament\Admin\Resources\RankActionResource\Pages;
use App\Models\Member;
use App\Models\RankAction;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class RankActionResource extends Resource
{
    protected static ?string $model = RankAction::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Division';

    protected static ?string $navigationParentItem = 'Divisions';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('member_id')
                    ->searchable()
                    ->getSearchResultsUsing(function (string $search): array {
                        $currentMember = auth()->user()->member;
                        return Member::query()
                            ->where('name', 'like', "%{$search}%")
                            ->where('id', '<>', $currentMember->id)
                            ->limit(10)
                            ->get()
                            ->mapWithKeys(fn ($member) => [$member->id => $member->present()->rankName()])
                            ->toArray();

                    })
                    ->getOptionLabelUsing(fn ($value): ?string => Member::find($value)?->present()->rankName()),

                Forms\Components\Select::make('rank')
                    ->options(Rank::class)
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
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
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
