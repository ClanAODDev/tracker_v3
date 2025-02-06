<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\LeaveResource\Pages;
use App\Filament\Mod\Resources\LeaveResource\RelationManagers\MemberRelationManager;
use App\Filament\Mod\Resources\LeaveResource\RelationManagers\NoteRelationManager;
use App\Models\Leave;
use App\Models\Note;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class LeaveResource extends Resource
{
    protected static ?string $model = Leave::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar';

    protected static ?string $label = 'Leave Request';

    protected static ?string $navigationGroup = 'Division';

    protected static ?string $pluralLabel = 'Leaves of Absence';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('member_id')
                    ->required()
                    ->exists('members', 'clan_id')
                    ->unique('leaves', 'member_id')
                    ->validationMessages([
                        'unique' => 'Member has an existing leave of absence',
                    ])
                    ->relationship('member', 'name')
                    ->hiddenOn('edit')
                    ->searchable(),

                Forms\Components\Select::make('reason')
                    ->label('Reason for leave')
                    ->required()
                    ->options(Leave::$reasons),

                Forms\Components\DateTimePicker::make('end_date')
                    ->after(now()->addDays(29))
                    ->default(now()->addDays(30))
                    ->validationMessages([
                        'after' => 'Date must be 30 days after today',
                    ])
                    ->required(),

                Forms\Components\Textarea::make('note.body')
                    ->label('Justification')
                    ->hiddenOn('edit')
                    ->columnSpanFull()
                    ->placeholder(function ($get) {
                        $note_id = $get('note_id');
                        $note = Note::find($note_id);

                        return ($note) ? $note->body : '';
                    })->disabledOn('edit'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('member.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('approver.name'),
                Tables\Columns\TextColumn::make('requester.name'),
                Tables\Columns\TextColumn::make('member.division.name'),
                Tables\Columns\TextColumn::make('reason'),
                Tables\Columns\TextColumn::make('end_date')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\Filter::make('needs approval')
                    ->query(fn (Builder $query): Builder => $query->whereNull('approver_id'))->default(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->label('Revoke')->requiresConfirmation(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            MemberRelationManager::class,
            NoteRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLeaves::route('/'),
            'create' => Pages\CreateLeave::route('/create'),
            'edit' => Pages\EditLeave::route('/{record}/edit'),
        ];
    }
}
