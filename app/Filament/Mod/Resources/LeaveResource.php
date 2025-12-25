<?php

namespace App\Filament\Mod\Resources;

use App\Filament\Mod\Resources\LeaveResource\Pages\CreateLeave;
use App\Filament\Mod\Resources\LeaveResource\Pages\EditLeave;
use App\Filament\Mod\Resources\LeaveResource\Pages\ListLeaves;
use App\Filament\Mod\Resources\LeaveResource\RelationManagers\MemberRelationManager;
use App\Filament\Mod\Resources\LeaveResource\RelationManagers\NoteRelationManager;
use App\Models\Leave;
use App\Models\Note;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class LeaveResource extends Resource
{
    protected static ?string $model = Leave::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-calendar';

    protected static ?string $label = 'Leave Request';

    protected static string|\UnitEnum|null $navigationGroup = 'Division';

    protected static ?string $pluralLabel = 'Leaves of Absence';

    public static function getNavigationBadge(): ?string
    {
        static $badge = null;

        if ($badge !== null) {
            return $badge;
        }

        if (auth()->user()->isRole(['admin', 'sr_ldr'])) {
            $divisionId = auth()->user()->member->division_id;

            $badge = (string) static::$model::where('approver_id', null)
                ->whereHas('member', function ($memberQuery) use ($divisionId) {
                    $memberQuery->where('division_id', $divisionId);
                })->count();

            return $badge;
        }

        return null;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('member_id')
                    ->required()
                    ->exists('members', 'clan_id')
                    ->unique('leaves', 'member_id')
                    ->validationMessages([
                        'unique' => 'Member has an existing leave of absence',
                    ])
                    ->relationship('member', 'name', function ($query) {
                        $query->where('division_id', auth()->user()->member->division_id);
                    })
                    ->hiddenOn('edit')
                    ->searchable(),

                Select::make('reason')
                    ->label('Reason for leave')
                    ->required()
                    ->options(Leave::$reasons),

                DateTimePicker::make('end_date')
                    ->after(now()->addDays(29))
                    ->default(now()->addDays(30))
                    ->validationMessages([
                        'after' => 'Date must be 30 days after today',
                    ])
                    ->required(),

                Textarea::make('note.body')
                    ->hiddenOn('edit')
                    ->label('Justification')
                    ->columnSpanFull()
                    ->placeholder(function ($get) {
                        $note_id = $get('note_id');
                        $note = Note::find($note_id);

                        return ($note) ? $note->body : '';
                    }),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('member.name')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('approver.name'),
                TextColumn::make('requester.name'),
                TextColumn::make('member.division.name'),
                TextColumn::make('reason'),
                TextColumn::make('end_date')
                    ->extraAttributes(fn (?Model $record) => $record->end_date < now()
                        ? ['style' => 'background-color: #ff1111; border-radius: 10px;']
                        : []
                    )
                    ->dateTime()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->modifyQueryUsing(function ($query) {
                $query->whereHas('member', function ($memberQuery) {
                    $memberQuery->where('division_id', auth()->user()->member->division_id);
                });
            })
            ->filters([
                Filter::make('needs approval')
                    ->query(fn (Builder $query): Builder => $query->whereNull('approver_id'))->default(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->label('Revoke')->requiresConfirmation(),
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
            'index' => ListLeaves::route('/'),
            'create' => CreateLeave::route('/create'),
            'edit' => EditLeave::route('/{record}/edit'),
        ];
    }
}
