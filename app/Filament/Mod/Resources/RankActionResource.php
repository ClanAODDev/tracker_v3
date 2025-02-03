<?php

namespace App\Filament\Mod\Resources;

use App\Enums\Rank;
use App\Filament\Mod\Resources\RankActionResource\Pages;
use App\Filament\Mod\Resources\RankActionResource\RelationManagers\MemberRelationManager;
use App\Filament\Mod\Resources\RankActionResource\RelationManagers\RequesterRelationManager;
use App\Models\RankAction;
use Filament\Forms;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class RankActionResource extends Resource
{
    protected static ?string $model = RankAction::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-plus';

    protected static ?string $navigationGroup = 'Division';

    protected static ?string $navigationParentItem = 'Members';

    public static function canEdit(Model $record): bool
    {
        return auth()->user()->isRole(['admin', 'sr_ldr']);
    }

    public static function canDeleteAny(): bool
    {
        return auth()->user()->isRole(['admin', 'sr_ldr']);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Wizard::make([
                    Wizard\Step::make('Select Member')
                        ->schema([
                            Forms\Components\Select::make('member_id')
                                ->relationship('member', 'name', function (Builder $query) {

                                    // if squad leader, only members of squad

                                    // if platoon leader, only members of platoon

                                    // if CO/XO, anyone in division

                                    // if sr_ldr, only platoon members
                                    $query->whereHas('division', function (Builder $subQuery) {
                                        $subQuery->where('active', true)
                                        ->where('squad_id', auth()->user()->member->squad_id);
                                    });
                                })
                                ->helperText(function () {
                                    if (auth()->user()->isSquadLeader()) {
                                        return "Only squad members can be selected";
                                    }

                                    if (auth()->user()->isPlatoonLeader()) {
                                        return "Only platoon members can be selected";
                                    }
                                })
                                ->searchable()
                                ->required(),
                        ]),
                    Wizard\Step::make('Select Rank')
                        ->schema([
                            Forms\Components\Select::make('rank')
                                ->options(Rank::class)
                                ->required(),
                        ]),
                ])->columnSpanFull(),





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
            RequesterRelationManager::class,
            MemberRelationManager::class,
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
