<?php

namespace App\Filament\Mod\Resources;

use App\Enums\TagVisibility;
use App\Filament\Mod\Resources\DivisionTagResource\Pages;
use App\Filament\Mod\Resources\DivisionTagResource\RelationManagers\MembersRelationManager;
use App\Models\DivisionTag;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class DivisionTagResource extends Resource
{
    protected static ?string $model = DivisionTag::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';

    protected static ?string $navigationGroup = 'Division';

    public static function getEloquentQuery(): Builder
    {
        $divisionId = auth()->user()->member?->division_id;

        return parent::getEloquentQuery()
            ->where('division_id', $divisionId);
    }

    public static function canAccess(): bool
    {
        return auth()->user()->can('viewAny', DivisionTag::class);
    }

    public static function form(Form $form): Form
    {
        $user = auth()->user();
        $isSeniorLeader = $user?->isRole(['admin', 'sr_ldr']) ?? false;

        $visibilityOptions = collect(TagVisibility::cases())
            ->when(! $isSeniorLeader, fn ($options) => $options->reject(
                fn ($v) => $v === TagVisibility::SENIOR_LEADERS
            ))
            ->mapWithKeys(fn ($visibility) => [$visibility->value => $visibility->label()]);

        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(25),
                Forms\Components\Select::make('visibility')
                    ->options($visibilityOptions)
                    ->default(TagVisibility::PUBLIC->value)
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('visibility')
                    ->badge()
                    ->formatStateUsing(fn (TagVisibility $state) => $state->label())
                    ->color(fn (TagVisibility $state) => match ($state) {
                        TagVisibility::PUBLIC => 'success',
                        TagVisibility::OFFICERS => 'warning',
                        TagVisibility::SENIOR_LEADERS => 'danger',
                    }),
                Tables\Columns\TextColumn::make('members_count')
                    ->counts('members')
                    ->label('Members'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            MembersRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDivisionTags::route('/'),
            'create' => Pages\CreateDivisionTag::route('/create'),
            'edit' => Pages\EditDivisionTag::route('/{record}/edit'),
        ];
    }
}
