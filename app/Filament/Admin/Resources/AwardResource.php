<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\AwardResource\Pages;
use App\Filament\Admin\Resources\MemberAwardResource\RelationManagers\MemberAwardsRelationManager;
use App\Models\Award;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class AwardResource extends Resource
{
    protected static ?string $model = Award::class;

    protected static ?string $navigationIcon = 'heroicon-o-trophy';

    protected static ?string $navigationGroup = 'Admin';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\FileUpload::make('image')
                    ->directory('awards')
                    ->rules(['dimensions:width=60,height=60'])
                    ->validationMessages([
                        'dimensions' => 'Award image size must be 60x60.',
                    ])
                    ->avatar()
                    ->columnSpanFull()
                    ->alignCenter()
                    ->image(),
                Forms\Components\TextInput::make('name')
                    ->columnSpanFull()
                    ->required()
                    ->maxLength(191),
                Forms\Components\Textarea::make('description')
                    ->columnSpanFull()
                    ->required()
                    ->maxLength(191),
                Forms\Components\Textarea::make('instructions')
                    ->placeholder('Ex. include a imgur link to screenshot, or link to game profile')
                    ->columnSpanFull()
                    ->required()
                    ->maxLength(191),
                Select::make('division_id')
                    ->relationship('division', 'name')
                    ->label('Division')
                    ->nullable(),
                Forms\Components\TextInput::make('display_order')
                    ->required()
                    ->numeric()
                    ->default(100),
                Forms\Components\Section::make('Metadata')->schema([
                    Forms\Components\Toggle::make('active')
                        ->default(true)
                        ->required(),
                    Forms\Components\Toggle::make('allow_request')
                        ->default(false)
                        ->required(),
                ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image'),
                Tables\Columns\TextColumn::make('name')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('description')
                    ->searchable()
                    ->limit(45)
                    ->toggleable(),
                Tables\Columns\TextInputColumn::make('display_order')
                    ->rules(['required', 'numeric'])
                    ->sortable(),
                Tables\Columns\ToggleColumn::make('allow_request'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])->defaultSort('display_order')
            ->filters([
                Tables\Filters\Filter::make('is_active')
                    ->query(fn (Builder $query): Builder => $query->where('active', true)),
                Tables\Filters\SelectFilter::make('division')
                    ->relationship('division', 'name')
                    ->multiple(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('update_division_id')
                        ->label('Mass assign to division')
                        ->form([
                            Forms\Components\Select::make('division_id')
                                ->relationship('division', 'name')
                                ->required(),
                        ])
                        ->action(function (Collection $records, array $data) {
                            $records->each(fn ($record) => $record->update(['division_id' => $data['division_id']]));
                        })
                        ->color('primary')
                        ->icon('heroicon-o-circle-stack')
                        ->deselectRecordsAfterCompletion(),
                ]),
            ])->filters([
                Tables\Filters\Filter::make('is_active_division')
                    ->query(fn ($query) => $query->active()),
                SelectFilter::make('by division')->relationship('division', 'name'),

            ]);
    }

    public static function getRelations(): array
    {
        return [
            MemberAwardsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAwards::route('/'),
            'create' => Pages\CreateAward::route('/create'),
            'edit' => Pages\EditAward::route('/{record}/edit'),
        ];
    }
}
