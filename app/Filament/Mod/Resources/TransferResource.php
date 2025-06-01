<?php

namespace App\Filament\Mod\Resources;

use App\Filament\Mod\Resources\TransferResource\Pages;
use App\Jobs\UpdateDivisionForMember;
use App\Models\Division;
use App\Models\Member;
use App\Models\Transfer;
use Closure;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\Wizard\Step;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TransferResource extends Resource
{
    protected static ?string $model = Transfer::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrows-right-left';

    protected static ?string $navigationGroup = 'Organization';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Wizard::make()
                    ->columnSpanFull()
                    ->label('Transfer Process')
                    ->steps([
                        Step::make('Member Transferring')
                            ->schema([
                                Select::make('member_id')
                                    ->label('Member')
                                    ->default(request('member_id') ?? null)
                                    ->disabledOn('edit')
                                    ->rules([
                                        function () {
                                            return function (string $attribute, $value, Closure $fail) {
                                                if (Transfer::where('member_id', $value)->pending()->exists()) {
                                                    $fail('This :attribute already has a pending transfer request.');
                                                }
                                            };
                                        },
                                    ])
                                    ->required()
                                    ->searchable()
                                    ->noSearchResultsMessage('No eligible members found')
                                    ->getOptionLabelUsing(fn ($value
                                    ): ?string => Member::find($value)?->present()->rankName())
                                    ->getSearchResultsUsing(function (string $search): array {
                                        return Member::query()
                                            ->where('name', 'like', "%{$search}%")
                                            ->whereNot('division_id', 0)
                                            ->get()
                                            ->mapWithKeys(fn ($member) => [
                                                $member->id => $member->present()->rankName(),
                                            ])
                                            ->toArray();
                                    })
                                    ->reactive() // Make it reactive
                                    ->afterStateUpdated(function ($state, callable $set, Forms\Get $get) {
                                        $memberId = $get('member_id');

                                        if ($memberId) {
                                            $member = Member::find($memberId);
                                            $set('current_division', $member ? $member->division->name : 'N/A');
                                        } else {
                                            $set('current_division', 'N/A');
                                        }
                                    }),

                                Forms\Components\Placeholder::make('current_division')
                                    ->label('Transferring From')
                                    ->content(fn (Forms\Get $get) => $get('current_division')
                                        ?? optional(Member::find($get('member_id')))->division->name
                                        ?? '--'
                                    ),
                            ]),

                        Step::make('Transferring To...')
                            ->schema([
                                Select::make('division_id')
                                    ->prefixIcon('heroicon-o-arrow-right')
                                    ->relationship(name: 'division',
                                        titleAttribute: 'name',
                                        modifyQueryUsing: fn (Builder $query, Forms\Get $get) => $query
                                            ->active()
                                            ->withoutFloaters()
                                            ->when(
                                                $get('member_id'),
                                                fn ($query) => $query->where('id', '!=',
                                                    optional(Member::find($get('member_id')))->division_id),
                                            )
                                    )
                                    ->label('Division')
                                    ->required()
                                    ->disabledOn('edit')
                                    ->default('division_id')
                                    ->helperText('Upon creation, both the assigned division and the new division will be notified of this transfer request.')
                                    ->reactive(),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('member.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('division.name')
                    ->label('Transferring To')
                    ->sortable(),
                Tables\Columns\TextColumn::make('member.rank')
                    ->label('Rank')
                    ->sortable()
                    ->toggleable()
                    ->badge(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Requested')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('division_id')
                    ->label('Division')
                    ->options(Division::active()->get()->pluck('name', 'id'))
                    ->default(auth()->user()->member->division_id),
                Tables\Filters\Filter::make('Incomplete')
                    ->query(function (Builder $query, array $data): Builder {
                        return empty($data) ? $query : $query
                            ->where(function (Builder $query) {
                                $query->where('approved_at', null);
                            });
                    })
                    ->default(),
            ])
            ->actions([
                Tables\Actions\DeleteAction::make(),

                Action::make('Approve')
                    ->button()
                    ->icon('heroicon-o-check')
                    ->requiresConfirmation()
                    ->action(function (Transfer $record) {
                        $record->approve();
                        UpdateDivisionForMember::dispatch($record);
                    })
                    ->visible(fn (Transfer $record) => $record->canApprove() && ! $record->approved_at),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    /*Tables\Actions\BulkAction::make(
                        'approve_selected')
                        ->label('Approve Selected')
                        ->icon('heroicon-o-check')
                        ->requiresConfirmation()
                        ->action(function (Collection $records) {
                            $records->each->approve();
                        }
                    ),*/
                    Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListTransfers::route('/'),
            'create' => Pages\CreateTransfer::route('/create'),
        ];
    }
}
