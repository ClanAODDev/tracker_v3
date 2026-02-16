<?php

namespace App\Filament\Admin\Resources;

use App\Enums\Rank;
use App\Filament\Admin\Resources\RankActionResource\Pages\CreateRankAction;
use App\Filament\Admin\Resources\RankActionResource\Pages\EditRankAction;
use App\Filament\Admin\Resources\RankActionResource\Pages\ListRankActions;
use App\Models\Member;
use App\Models\RankAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class RankActionResource extends Resource
{
    protected static ?string $model = RankAction::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static string|\UnitEnum|null $navigationGroup = 'Division';

    protected static ?string $navigationParentItem = 'Divisions';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
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

                Select::make('rank')
                    ->options(Rank::class)
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('member.name'),
                TextColumn::make('rank')
                    ->sortable()
                    ->badge(),
                TextColumn::make('created_at')
                    ->label('Rank Changed At')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('member_id')
                    ->label('Member')
                    ->searchable()
                    ->relationship('member', 'name'),
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
            'index'  => ListRankActions::route('/'),
            'create' => CreateRankAction::route('/create'),
            'edit'   => EditRankAction::route('/{record}/edit'),
        ];
    }
}
