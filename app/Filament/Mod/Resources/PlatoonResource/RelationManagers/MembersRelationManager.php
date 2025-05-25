<?php

namespace App\Filament\Mod\Resources\PlatoonResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Table;
use Illuminate\Support\Collection;

class MembersRelationManager extends RelationManager
{
    protected static string $relationship = 'members';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('rank')
                    ->sortable()
                    ->badge(),
                Tables\Columns\TextColumn::make('position'),
                Tables\Columns\TextColumn::make('squad.name')
                    ->toggleable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                //                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                //                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    //                    Tables\Actions\DeleteBulkAction::make(),
                    BulkAction::make('member_transfer')
                        ->label('Transfer member(s)')
                        ->visible(fn (): bool => auth()->user()->isRole(['admin', 'sr_ldr'])
                            || (auth()->user()->isPlatoonLeader() && auth()->user()->member->clan_id ==
                                $this->ownerRecord->leader_id)
                        )
                        ->icon('heroicon-o-adjustments-vertical')
                        ->form([
                            Select::make('squad_id')
                                ->label('Squad')
                                ->options(fn () => \App\Models\Squad::where('platoon_id', $this->ownerRecord->id)
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
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion()
                        ->color('primary'),
                ]),
            ]);
    }
}
