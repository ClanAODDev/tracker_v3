<?php

namespace App\Filament\Admin\Resources;

use App\Enums\TagVisibility;
use App\Filament\Admin\Resources\GlobalTagResource\Pages;
use App\Models\DivisionTag;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class GlobalTagResource extends Resource
{
    protected static ?string $model = DivisionTag::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';

    protected static ?string $navigationGroup = 'Admin';

    protected static ?string $modelLabel = 'Global Tag';

    protected static ?string $pluralModelLabel = 'Global Tags';

    protected static ?string $slug = 'global-tags';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->whereNull('division_id');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(50),
                Forms\Components\Select::make('visibility')
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListGlobalTags::route('/'),
            'create' => Pages\CreateGlobalTag::route('/create'),
            'edit' => Pages\EditGlobalTag::route('/{record}/edit'),
        ];
    }
}
