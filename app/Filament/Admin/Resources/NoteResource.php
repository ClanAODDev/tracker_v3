<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\NoteResource\Pages\CreateNote;
use App\Filament\Admin\Resources\NoteResource\Pages\EditNote;
use App\Filament\Admin\Resources\NoteResource\Pages\ListNotes;
use App\Models\Note;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class NoteResource extends Resource
{
    protected static ?string $model = Note::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static string|\UnitEnum|null $navigationGroup = 'Division';

    protected static ?string $navigationParentItem = 'Members';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Textarea::make('body')
                    ->required()
                    ->columnSpanFull(),
                TextInput::make('forum_thread_id')
                    ->numeric()
                    ->default(null),
                Select::make('member')
                    ->relationship('member', 'name')
                    ->searchable(),
                Select::make('author')
                    ->relationship('author', 'name')
                    ->searchable(),
                Select::make('type')
                    ->options(collect(Note::allNoteTypes()))
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('member.name')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('body')
                    ->limit(60),
                TextColumn::make('type'),
                TextColumn::make('author.name')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
                SelectFilter::make('member')
                    ->searchable()
                    ->relationship('member', 'name'),
                SelectFilter::make('type')
                    ->options(collect(Note::allNoteTypes())),
                SelectFilter::make('division')
                    ->searchable()
                    ->relationship('member.division', 'name'),
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListNotes::route('/'),
            'create' => CreateNote::route('/create'),
            'edit' => EditNote::route('/{record}/edit'),
        ];
    }
}
