<?php

namespace App\Filament\Admin\Resources\TrainingModuleResource\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SectionsRelationManager extends RelationManager
{
    protected static string $relationship = 'sections';

    protected static ?string $title = 'Sections';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->required()
                    ->maxLength(191)
                    ->columnSpan(2),
                TextInput::make('icon')
                    ->placeholder('fa-tasks')
                    ->helperText('FontAwesome icon class (e.g., fa-tasks, fa-sitemap)')
                    ->maxLength(50),
                TextInput::make('display_order')
                    ->numeric()
                    ->default(0),
                MarkdownEditor::make('content')
                    ->required()
                    ->columnSpanFull()
                    ->fileAttachmentsDirectory('training'),
                Repeater::make('checkpoints')
                    ->relationship()
                    ->columnSpanFull()
                    ->orderColumn('display_order')
                    ->reorderable()
                    ->collapsible()
                    ->defaultItems(0)
                    ->itemLabel(fn (array $state): ?string => $state['label'] ?? null)
                    ->schema([
                        TextInput::make('label')
                            ->required()
                            ->maxLength(191)
                            ->columnSpanFull(),
                        MarkdownEditor::make('description')
                            ->columnSpanFull()
                            ->fileAttachmentsDirectory('training')
                            ->helperText('Optional expandable content shown when the task is clicked'),
                    ]),
            ])
            ->columns(4);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->reorderable('display_order')
            ->defaultSort('display_order')
            ->paginated(false)
            ->columns([
                TextColumn::make('display_order')
                    ->label('#')
                    ->sortable()
                    ->width(50),
                TextColumn::make('icon')
                    ->formatStateUsing(fn (string $state): string => "<i class=\"fa {$state}\"></i>")
                    ->html()
                    ->width(50),
                TextColumn::make('title')
                    ->searchable(),
                TextColumn::make('checkpoints_count')
                    ->counts('checkpoints')
                    ->label('Checkpoints'),
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([]);
    }
}
