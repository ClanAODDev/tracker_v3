<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\AwardResource\Pages\CreateAward;
use App\Filament\Admin\Resources\AwardResource\Pages\EditAward;
use App\Filament\Admin\Resources\AwardResource\Pages\ListAwards;
use App\Filament\Admin\Resources\MemberAwardResource\RelationManagers\MemberAwardsRelationManager;
use App\Models\Award;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\TextInputColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class AwardResource extends Resource
{
    protected static ?string $model = Award::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-trophy';

    protected static string|\UnitEnum|null $navigationGroup = 'Admin';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                FileUpload::make('image')
                    ->disk('public')
                    ->directory('awards')
                    ->imageResizeMode('force')
                    ->imageResizeTargetWidth(60)
                    ->imageResizeTargetHeight(60)
                    ->avatar()
                    ->columnSpanFull()
                    ->alignCenter()
                    ->image(),
                TextInput::make('name')
                    ->columnSpanFull()
                    ->required()
                    ->maxLength(191),
                Textarea::make('description')
                    ->columnSpanFull()
                    ->required()
                    ->maxLength(191),
                Textarea::make('instructions')
                    ->placeholder('Ex. include a imgur link to screenshot, or link to game profile')
                    ->columnSpanFull()
                    ->nullable()
                    ->maxLength(191),
                Select::make('division_id')
                    ->relationship('division', 'name')
                    ->label('Division')
                    ->nullable(),
                Select::make('prerequisite_award_id')
                    ->relationship('prerequisite', 'name')
                    ->label('Prerequisite Award')
                    ->helperText('For tiered awards, select the lower tier that must be earned first')
                    ->searchable()
                    ->nullable()
                    ->live(),
                TextInput::make('tiered_group_name')
                    ->label('Tiered Group Name')
                    ->helperText('Custom name for the tiered award group (e.g., "AOD Tenure"). Only set on the base tier.')
                    ->maxLength(100)
                    ->nullable()
                    ->visible(fn ($get) => $get('prerequisite_award_id') === null),
                Textarea::make('tiered_group_description')
                    ->label('Tiered Group Description')
                    ->helperText('Description shown on the tiered award progression page. Only set on the base tier.')
                    ->maxLength(500)
                    ->rows(3)
                    ->nullable()
                    ->visible(fn ($get) => $get('prerequisite_award_id') === null),
                TextInput::make('display_order')
                    ->required()
                    ->numeric()
                    ->default(100),
                Section::make('Metadata')
                    ->columnSpanFull()
                    ->schema([
                        Toggle::make('active')
                            ->default(true)
                            ->required(),
                        Toggle::make('allow_request')
                            ->default(false)
                            ->required(),
                        Toggle::make('repeatable')
                            ->label('Can be earned multiple times')
                            ->default(false)
                            ->required(),
                    ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->withCount('recipients'))
            ->columns([
                ImageColumn::make('image'),
                TextColumn::make('name')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('description')
                    ->searchable()
                    ->limit(45)
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('recipients_count')
                    ->label('Recipients')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextInputColumn::make('display_order')
                    ->rules(['required', 'numeric'])
                    ->sortable()
                    ->width('80px')
                    ->toggleable(isToggledHiddenByDefault: true),
                ToggleColumn::make('allow_request'),
                ToggleColumn::make('repeatable')
                    ->label('Multi'),
                TextColumn::make('prerequisite.name')
                    ->label('Prerequisite')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])->defaultSort('display_order')
            ->filters([
                Filter::make('is_active')
                    ->query(fn (Builder $query): Builder => $query->where('active', true)),
                Filter::make('is_active_division')
                    ->query(fn (Builder $query): Builder => $query->active()),
                SelectFilter::make('division')
                    ->relationship('division', 'name')
                    ->multiple(),
            ])
            ->filtersLayout(FiltersLayout::AboveContentCollapsible)
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    BulkAction::make('update_division_id')
                        ->label('Mass assign to division')
                        ->form([
                            Select::make('division_id')
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
            'index' => ListAwards::route('/'),
            'create' => CreateAward::route('/create'),
            'edit' => EditAward::route('/{record}/edit'),
        ];
    }
}
