<?php

namespace App\Filament\Mod\Resources\UnitResource\RelationManagers;

use App\Filament\Mod\Resources\UnitResource;
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

class ChildrenRelationManager extends RelationManager
{
    protected static string $relationship = 'children';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return $ownerRecord->childLevel()?->label_plural ?? 'Units';
    }

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return $ownerRecord->childLevel() !== null;
    }

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
            ->modelLabel($this->getOwnerRecord()->childLevel()?->label ?? 'Unit')
            ->recordTitleAttribute('name')
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

                    return $units->create($this->getOwnerRecord()->division, $this->getOwnerRecord(), $data);
                }),
            ])
            ->recordActions([
                EditAction::make()->url(fn (Model $record): string => UnitResource::getUrl('edit', ['record' => $record])),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                ]),
            ]);
    }
}
