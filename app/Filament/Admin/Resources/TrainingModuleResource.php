<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\TrainingModuleResource\Pages\CreateTrainingModule;
use App\Filament\Admin\Resources\TrainingModuleResource\Pages\EditTrainingModule;
use App\Filament\Admin\Resources\TrainingModuleResource\Pages\ListTrainingModules;
use App\Filament\Admin\Resources\TrainingModuleResource\RelationManagers\SectionsRelationManager;
use App\Models\TrainingModule;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\TextInputColumn;
use Filament\Tables\Table;

class TrainingModuleResource extends Resource
{
    protected static ?string $model = TrainingModule::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-academic-cap';

    protected static string|\UnitEnum|null $navigationGroup = 'Admin';

    protected static ?string $modelLabel = 'Training Module';

    protected static ?string $pluralModelLabel = 'Training Modules';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(191),
                TextInput::make('slug')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(50)
                    ->alphaDash(),
                Textarea::make('description')
                    ->columnSpanFull()
                    ->rows(2)
                    ->maxLength(500),
                TextInput::make('display_order')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('checkpoint_label')
                    ->default('Talking Points')
                    ->maxLength(50)
                    ->helperText('Label shown above checkpoints (e.g., "Talking Points", "Tasks")'),
                Toggle::make('is_active')
                    ->default(true),
                Toggle::make('show_completion_form')
                    ->label('Show Completion Form')
                    ->default(true)
                    ->helperText('Show the trainee search form at the bottom'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->reorderable('display_order')
            ->defaultSort('display_order')
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('slug')
                    ->searchable()
                    ->badge()
                    ->color('gray'),
                TextColumn::make('sections_count')
                    ->counts('sections')
                    ->label('Sections'),
                TextInputColumn::make('display_order')
                    ->rules(['required', 'numeric'])
                    ->sortable(),
                IconColumn::make('is_active')
                    ->boolean()
                    ->label('Active'),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordActions([
                EditAction::make(),
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
            SectionsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTrainingModules::route('/'),
            'create' => CreateTrainingModule::route('/create'),
            'edit' => EditTrainingModule::route('/{record}/edit'),
        ];
    }
}
