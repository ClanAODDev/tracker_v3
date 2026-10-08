<?php

namespace App\Filament\Mod\Resources\UnitResource\RelationManagers;

use App\Authorization\UnitHierarchy;
use App\Enums\Ability;
use App\Enums\UnitLevel;
use App\Models\Unit;
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
    protected static string $relationship = 'subtreeMembers';

    protected static ?string $title = 'Members';

    protected static ?string $modelLabel = 'member';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    private function descendantUnits(): Collection
    {
        return Unit::query()
            ->where('path', 'like', $this->ownerRecord->path . '%')
            ->orderBy('path')
            ->get();
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
                TextColumn::make('unit')
                    ->state(fn ($record) => $record->unit?->name)
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
                        ->visible(fn (): bool => auth()->user()->can(Ability::TransferMembers)
                            || (app(UnitHierarchy::class)->leadershipLevel(auth()->user()) === UnitLevel::Platoon
                                && app(UnitHierarchy::class)->leadsWithin(auth()->user()->member, $this->ownerRecord))
                        )
                        ->icon('heroicon-o-adjustments-vertical')
                        ->form([
                            Select::make('unit_id')
                                ->label('Unit')
                                ->options(fn () => $this->descendantUnits()
                                    ->mapWithKeys(fn (Unit $unit) => [$unit->id => str_repeat('— ', $unit->depth - $this->ownerRecord->depth) . ($unit->name ?: 'Untitled')]))
                                ->searchable()
                                ->required(),
                        ])
                        ->action(function (Collection $records, array $data): void {
                            $unit = $this->descendantUnits()->firstWhere('id', $data['unit_id']);

                            abort_if($unit === null, 404);

                            $records->each(fn ($member) => $member->update(['unit_id' => $unit->id]));
                        })
                        ->deselectRecordsAfterCompletion()
                        ->color('primary'),
                ]),
            ]);
    }
}
