<?php

namespace App\Filament\Mod\Resources;

use App\Filament\Mod\Resources\MemberAwardResource\Pages;
use App\Filament\Mod\Resources\MemberAwardResource\RelationManagers\AwardRelationManager;
use App\Models\MemberAward;
use App\Notifications\MemberAwarded;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class MemberAwardResource extends Resource
{
    protected static ?string $model = MemberAward::class;

    protected static ?string $navigationIcon = 'heroicon-o-trophy';

    protected static ?string $navigationGroup = 'Division';

    protected static ?string $navigationParentItem = 'Members';

    public static function canDeleteAny(): bool
    {
        return auth()->user()->isRole(['admin', 'sr_ldr']);
    }

    public static function canEdit(Model $record): bool
    {
        return auth()->user()->isRole(['admin', 'sr_ldr']);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('award_id')
                    ->relationship('award', 'name')
                    ->hiddenOn('edit'),

                Forms\Components\Select::make('member_id')
                    ->relationship('member', 'name', function (Builder $query) {
                        $query->whereHas('division', function (Builder $subQuery) {
                            $subQuery->where('active', true);
                        });
                    })
                    ->columnSpanFull()
                    ->searchable()
                    ->required(),

                Forms\Components\Textarea::make('reason')
                    ->columnSpanFull()
                    ->rows(5),

                Forms\Components\Section::make('Metadata')->schema([
                    Forms\Components\DateTimePicker::make('created_at')->default(now()),
                    Forms\Components\DateTimePicker::make('updated_at')->default(now()),
                ])->columns()->hiddenOn(['edit', 'create']),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('award.name')
                    ->numeric()
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('member.name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('reason')
                    ->label('Justification')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('division.name'),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters(filters: [
                Tables\Filters\Filter::make('needs approval')
                    ->query(fn (Builder $query): Builder => $query->where('approved', false))->default(),
                SelectFilter::make('by division')->relationship('division', 'name'),
                SelectFilter::make('award')->relationship('award', 'name'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),

                    Tables\Actions\BulkAction::make('approve')
                        ->label('Approve')
                        ->hidden(fn() => !auth()->user()->isRole(['admin', 'sr_ldr']))
                        ->action(fn (Collection $records) => $records->each->update(['approved' => true])),

                    Tables\Actions\BulkAction::make('approve_and_notify')
                        ->label('Approve and Notify')
                        ->hidden(fn() => !auth()->user()->isRole(['admin', 'sr_ldr']))
                        ->action(fn (Collection $records) => $records->each->update(['approved' => true]))
                        ->requiresConfirmation()
                        ->modalDescription('This will generate a notification for every award approved.')
                        ->after(function (Collection $records) {
                            foreach ($records as $memberAward) {
                                if ($memberAward->member->division->settings()->get('chat_alerts.member_awarded')) {
                                    $memberAward->member->division->notify(new MemberAwarded(
                                        $memberAward->member->name,
                                        $memberAward->award
                                    ));
                                }
                            }
                        }),

                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            AwardRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMemberAwards::route('/'),
            'create' => Pages\CreateMemberAward::route('/create'),
            'edit' => Pages\EditMemberAward::route('/{record}/edit'),
        ];
    }
}
