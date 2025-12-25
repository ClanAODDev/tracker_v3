<?php

namespace App\Filament\Mod\Resources\PlatoonResource\RelationManagers;

use App\Models\Squad;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Collection;

class MembersRelationManager extends RelationManager
{
    protected static string $relationship = 'members';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('rank')
                    ->sortable()
                    ->badge(),
                TextColumn::make('position'),
                TextColumn::make('squad.name')
                    ->toggleable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                //                Tables\Actions\CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                //                Tables\Actions\DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    //                    Tables\Actions\DeleteBulkAction::make(),
                    BulkAction::make('member_transfer')
                        ->label('Transfer member(s)')
                        ->modalWidth('lg')
                        ->modalDescription('Only members of the same division can be transferred.')
                        ->visible(fn (): bool => auth()->user()->isRole(['admin', 'sr_ldr'])
                            || (auth()->user()->isPlatoonLeader() && auth()->user()->member->clan_id ==
                                $this->ownerRecord->leader_id)
                        )
                        ->icon('heroicon-o-adjustments-vertical')
                        ->form([
                            Select::make('squad_id')
                                ->label('Squad')
                                ->options(fn () => Squad::where('platoon_id', $this->ownerRecord->id)
                                    ->pluck('name', 'id'))
                                ->searchable()
                                ->required(),
                        ])
                        ->action(function (Collection $records, array $data): void {
                            $records->each(function ($member) use ($data) {
                                $member->update([
                                    'squad_id' => $data['squad_id'],
                                ]);
                            });
                        })
                        ->deselectRecordsAfterCompletion()
                        ->color('primary'),
                ]),
            ]);
    }
}
