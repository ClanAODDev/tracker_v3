<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\ReleaseNoteResource\Pages\CreateReleaseNote;
use App\Filament\Admin\Resources\ReleaseNoteResource\Pages\EditReleaseNote;
use App\Filament\Admin\Resources\ReleaseNoteResource\Pages\ListReleaseNotes;
use App\Models\ReleaseNote;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ReleaseNoteResource extends Resource
{
    protected static ?string $model = ReleaseNote::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-megaphone';

    protected static string|\UnitEnum|null $navigationGroup = 'Configuration';

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'Release Note';

    protected static ?string $pluralModelLabel = 'Release Notes';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                TextInput::make('title')
                    ->required()
                    ->maxLength(191),
                TextInput::make('slug')
                    ->required(fn (string $operation): bool => $operation === 'edit')
                    ->maxLength(191)
                    ->alphaDash()
                    ->unique(ignoreRecord: true)
                    ->helperText('Used in the link to this note. Leave empty on a new note to generate it from the title.'),
                TextInput::make('version')
                    ->maxLength(50)
                    ->placeholder('e.g. 3.9')
                    ->helperText('Optional label shown next to the title.'),
                DateTimePicker::make('published_at')
                    ->label('Publish at')
                    ->seconds(false)
                    ->helperText('Leave empty to keep this as a draft. A future date schedules it; it appears for members once that time passes.')
                    ->columnSpanFull(),
                MarkdownEditor::make('body')
                    ->required()
                    ->columnSpanFull()
                    ->minHeight('24rem'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('published_at', 'desc')
            ->columns([
                TextColumn::make('title')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('version')
                    ->badge()
                    ->color('gray')
                    ->placeholder('—'),
                TextColumn::make('status')
                    ->badge()
                    ->state(fn (ReleaseNote $record): string => $record->status())
                    ->color(fn (string $state): string => match ($state) {
                        'published' => 'success',
                        'scheduled' => 'warning',
                        default     => 'gray',
                    }),
                TextColumn::make('published_at')
                    ->label('Publish at')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('—'),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordActions([
                Action::make('publish')
                    ->label('Publish now')
                    ->icon('heroicon-o-megaphone')
                    ->requiresConfirmation()
                    ->visible(fn (ReleaseNote $record): bool => $record->status() !== 'published')
                    ->action(fn (ReleaseNote $record) => $record->update(['published_at' => now()])),
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
            'index'  => ListReleaseNotes::route('/'),
            'create' => CreateReleaseNote::route('/create'),
            'edit'   => EditReleaseNote::route('/{record}/edit'),
        ];
    }
}
