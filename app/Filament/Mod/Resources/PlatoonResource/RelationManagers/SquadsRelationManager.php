<?php

namespace App\Filament\Mod\Resources\PlatoonResource\RelationManagers;

use App\Filament\Mod\Resources\SquadResource;
use App\Models\Squad;
use App\Rules\ResolvesToImage;
use App\Services\Units\UnitAssignment;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class SquadsRelationManager extends RelationManager
{
    protected static string $relationship = 'Squads';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->required()
                ->maxLength(255),
            TextInput::make('logo')
                ->maxLength(191)
                ->placeholder('https://')
                ->default(null)
                ->rule(new ResolvesToImage),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('Squad')
            ->columns([
                TextColumn::make('name'),
                TextColumn::make('leader.name')
                    ->default('--')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make()->using(function (array $data) {
                    $units = app(UnitAssignment::class);
                    $unit  = $units->create($this->getOwnerRecord()->division, $units->forLegacy($this->getOwnerRecord()), $data);

                    return Squad::findOrFail($unit->legacy_id);
                }),
            ])
            ->recordActions([
                EditAction::make()->url(fn (Model $record): string => SquadResource::getUrl('edit',
                    ['record' => $record])),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                ]),
            ]);
    }
}
