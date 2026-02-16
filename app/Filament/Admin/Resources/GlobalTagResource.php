<?php

namespace App\Filament\Admin\Resources;

use App\Enums\TagVisibility;
use App\Filament\Admin\Resources\GlobalTagResource\Pages\CreateGlobalTag;
use App\Filament\Admin\Resources\GlobalTagResource\Pages\EditGlobalTag;
use App\Filament\Admin\Resources\GlobalTagResource\Pages\ListGlobalTags;
use App\Filament\Admin\Resources\GlobalTagResource\RelationManagers\MembersRelationManager;
use App\Models\DivisionTag;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class GlobalTagResource extends Resource
{
    protected static ?string $model = DivisionTag::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-tag';

    protected static string|\UnitEnum|null $navigationGroup = 'Admin';

    protected static ?string $modelLabel = 'Global Tag';

    protected static ?string $pluralModelLabel = 'Global Tags';

    protected static ?string $slug = 'global-tags';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->whereNull('division_id');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(25),
                Select::make('visibility')
                    ->options(collect(TagVisibility::cases())
                        ->mapWithKeys(fn ($v) => [$v->value => $v->label()]))
                    ->default(TagVisibility::PUBLIC->value)
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('visibility')
                    ->badge()
                    ->formatStateUsing(fn (TagVisibility $state) => $state->label())
                    ->color(fn (TagVisibility $state) => match ($state) {
                        TagVisibility::PUBLIC         => 'success',
                        TagVisibility::OFFICERS       => 'warning',
                        TagVisibility::SENIOR_LEADERS => 'danger',
                    }),
                TextColumn::make('members_count')
                    ->counts('members')
                    ->label('Members'),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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

    public static function getRelations(): array
    {
        return [
            MembersRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListGlobalTags::route('/'),
            'create' => CreateGlobalTag::route('/create'),
            'edit'   => EditGlobalTag::route('/{record}/edit'),
        ];
    }
}
