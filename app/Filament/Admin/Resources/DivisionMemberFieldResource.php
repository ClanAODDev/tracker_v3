<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\DivisionMemberFieldResource\Pages\EditDivisionMemberField;
use App\Filament\Admin\Resources\DivisionMemberFieldResource\Pages\ListDivisionMemberFields;
use App\Filament\Forms\Components\DivisionMemberFieldForm;
use App\Models\Division;
use App\Models\DivisionMemberField;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class DivisionMemberFieldResource extends Resource
{
    protected static ?string $model = DivisionMemberField::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-table-cells';

    protected static string|\UnitEnum|null $navigationGroup = 'Configuration';

    protected static ?string $modelLabel = 'Member Field';

    protected static ?string $pluralModelLabel = 'Member Fields';

    protected static ?string $slug = 'member-fields';

    public static function form(Schema $schema): Schema
    {
        return $schema->components(DivisionMemberFieldForm::schema());
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('division.name')
            ->columns([
                TextColumn::make('division.name')
                    ->label('Division')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('label')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('key')
                    ->badge()
                    ->color('gray'),
                TextColumn::make('type')
                    ->badge(),
                TextColumn::make('options')
                    ->label('Choices')
                    ->formatStateUsing(fn (mixed $state) => DivisionMemberFieldForm::formatChoices($state))
                    ->wrap(),
                TextColumn::make('values_count')
                    ->counts('values')
                    ->label('Members set'),
                IconColumn::make('filterable')
                    ->boolean(),
                IconColumn::make('self_editable')
                    ->label('Self-Editable')
                    ->boolean(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('division_id')
                    ->label('Division')
                    ->options(fn () => Division::orderBy('name')->pluck('name', 'id')),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDivisionMemberFields::route('/'),
            'edit'  => EditDivisionMemberField::route('/{record}/edit'),
        ];
    }
}
