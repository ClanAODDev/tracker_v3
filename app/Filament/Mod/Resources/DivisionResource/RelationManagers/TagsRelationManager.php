<?php

namespace App\Filament\Mod\Resources\DivisionResource\RelationManagers;

use App\Enums\TagVisibility;
use App\Models\DivisionTag;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class TagsRelationManager extends RelationManager
{
    protected static string $relationship = 'tags';

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return auth()->user()->can('viewAny', DivisionTag::class);
    }

    public function form(Form $form): Form
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
                    ->maxLength(50),
                Forms\Components\Select::make('visibility')
                    ->options($visibilityOptions)
                    ->default(TagVisibility::PUBLIC->value)
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
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
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->visible(fn () => auth()->user()->can('create', DivisionTag::class)),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->visible(fn (DivisionTag $record) => auth()->user()->can('update', $record)),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn (DivisionTag $record) => auth()->user()->can('delete', $record)),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn () => auth()->user()->can('create', DivisionTag::class)),
                ]),
            ]);
    }
}
