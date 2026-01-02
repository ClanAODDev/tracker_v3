<?php

namespace App\Filament\Mod\Resources;

use App\Enums\TagVisibility;
use App\Filament\Mod\Resources\DivisionTagResource\Pages\CreateDivisionTag;
use App\Filament\Mod\Resources\DivisionTagResource\Pages\EditDivisionTag;
use App\Filament\Mod\Resources\DivisionTagResource\Pages\ListDivisionTags;
use App\Filament\Mod\Resources\DivisionTagResource\Pages\ViewDivisionTag;
use App\Filament\Mod\Resources\DivisionTagResource\RelationManagers\MembersRelationManager;
use App\Models\DivisionTag;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class DivisionTagResource extends Resource
{
    protected static ?string $model = DivisionTag::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-tag';

    protected static string|\UnitEnum|null $navigationGroup = 'Division';

    public static function getEloquentQuery(): Builder
    {
        $user = auth()->user();
        $divisionId = $user->member?->division_id;

        return parent::getEloquentQuery()
            ->forDivision($divisionId)
            ->visibleTo($user);
    }

    public static function canAccess(): bool
    {
        return auth()->user()->can('viewAny', DivisionTag::class);
    }

    public static function form(Schema $schema): Schema
    {
        $user = auth()->user();
        $isSeniorLeader = $user?->isRole(['admin', 'sr_ldr']) ?? false;

        $visibilityOptions = collect(TagVisibility::cases())
            ->when(! $isSeniorLeader, fn ($options) => $options->reject(
                fn ($v) => $v === TagVisibility::SENIOR_LEADERS
            ))
            ->mapWithKeys(fn ($visibility) => [$visibility->value => $visibility->label()]);

        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(25),
                Select::make('visibility')
                    ->options($visibilityOptions)
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
                TextColumn::make('scope')
                    ->label('Scope')
                    ->badge()
                    ->getStateUsing(fn (DivisionTag $record) => $record->isGlobal() ? 'Clan-wide' : 'Division')
                    ->color(fn (DivisionTag $record) => $record->isGlobal() ? 'info' : 'gray'),
                TextColumn::make('visibility')
                    ->badge()
                    ->formatStateUsing(fn (TagVisibility $state) => $state->label())
                    ->color(fn (TagVisibility $state) => match ($state) {
                        TagVisibility::PUBLIC => 'success',
                        TagVisibility::OFFICERS => 'warning',
                        TagVisibility::SENIOR_LEADERS => 'danger',
                    }),
                TextColumn::make('members_count')
                    ->label('Members')
                    ->getStateUsing(function (DivisionTag $record) {
                        $divisionId = auth()->user()->member?->division_id;

                        return $record->members()->where('members.division_id', $divisionId)->count();
                    }),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make()
                    ->visible(fn (DivisionTag $record) => ! $record->isGlobal()),
                DeleteAction::make()
                    ->visible(fn (DivisionTag $record) => ! $record->isGlobal() || auth()->user()->isRole('admin')),
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
            'index' => ListDivisionTags::route('/'),
            'create' => CreateDivisionTag::route('/create'),
            'view' => ViewDivisionTag::route('/{record}'),
            'edit' => EditDivisionTag::route('/{record}/edit'),
        ];
    }
}
