<?php

namespace App\Filament\Mod\Resources\DivisionResource\RelationManagers;

use App\Filament\Mod\Resources\UnitResource;
use App\Models\Unit;
use App\Rules\ResolvesToImage;
use App\Services\Units\UnitAssignment;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Actions\RestoreAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\TextInputColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PlatoonsRelationManager extends RelationManager
{
    protected static string $relationship = 'topUnits';

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return ! $ownerRecord->isFlat();
    }

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return $ownerRecord->unitLevel(1)?->label_plural ?? 'Platoons';
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('logo')
                    ->placeholder('https://')
                    ->maxLength(255)
                    ->default(null)
                    ->rule(new ResolvesToImage),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->modelLabel(strtolower($this->getOwnerRecord()->unitLevel(1)?->label ?? 'platoon'))
            ->columns([
                TextInputColumn::make('order')
                    ->width('10px')
                    ->sortable()
                    ->updateStateUsing(function (Unit $record, $state) {
                        app(UnitAssignment::class)->update($record, ['order' => (int) $state]);

                        return $state;
                    }),
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('leader.name')
                    ->numeric()
                    ->default('--')
                    ->sortable(),
                TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->visible(fn () => auth()->user()->can('create', Unit::class))
                    ->using(function (array $data) {
                        return app(UnitAssignment::class)->create($this->getOwnerRecord(), null, $data);
                    }),
            ])
            ->recordActions([
                EditAction::make()->url(fn (Model $record): string => UnitResource::getUrl('edit',
                    ['record' => $record])),
                RestoreAction::make()->using(function (Unit $record) {
                    app(UnitAssignment::class)->restore($record);

                    return true;
                }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    //                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
