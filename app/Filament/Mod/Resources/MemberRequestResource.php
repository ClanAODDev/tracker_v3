<?php

namespace App\Filament\Mod\Resources;

use App\Enums\Position;
use App\Filament\Mod\Resources\MemberRequestResource\Pages;
use App\Models\MemberRequest;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class MemberRequestResource extends Resource
{
    protected static ?string $model = MemberRequest::class;

    protected static ?string $navigationIcon = 'heroicon-o-inbox';

    protected static ?string $navigationLabel = 'Member Requests';

    protected static ?string $pluralLabel = 'Member Requests';

    protected static ?string $navigationGroup = 'Division';

    public static function getNavigationBadge(): ?string
    {
        $user = auth()->user();
        if (! $user) {
            return null;
        }

        $query = MemberRequest::query()->pending();

        if (self::isDivisionLeadership() && ! $user->isRole('admin')) {
            $divisionId = $user->member?->division_id;
            if ($divisionId) {
                $query->where('division_id', $divisionId);
            }
        }

        $count = $query->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        $user = auth()->user();
        $scope = (self::isDivisionLeadership() && ! $user?->isRole('admin')) ? 'your division' : 'all divisions';

        return "Pending requests in {$scope}";
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->can('manage', MemberRequest::class) ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Wizard::make([

                Forms\Components\Wizard\Step::make('Pending Approval')
                    ->icon('heroicon-o-clock')
                    ->schema([
                        Forms\Components\Section::make('Request Details')
                            ->description('Review the request before acting.')
                            ->columns(3)
                            ->schema([
                                Forms\Components\Placeholder::make('Division')
                                    ->content(fn (?Model $record): string => $record ? $record->division->name : ''),
                                Forms\Components\Placeholder::make('Member')
                                    ->content(fn (?Model $record): string => $record ? $record->member->name : ''),
                                Forms\Components\Placeholder::make('Requester')
                                    ->content(fn (?Model $record): string => $record
                                        ? $record->requester->present()->rankName
                                        : ''
                                    ),
                                Forms\Components\Placeholder::make('Created At')
                                    ->content(fn (?Model $record): string => $record
                                        ? $record->created_at?->toDayDateTimeString() . ' - ' . $record->created_at?->diffForHumans()
                                        : ''
                                    ),
                            ]),
                    ]),

                Forms\Components\Wizard\Step::make('On Hold')
                    ->icon('heroicon-o-pause-circle')
                    ->label(fn (?Model $record): string => 'On Hold' . ($record && $record->holder ? ' - 
                    ' . $record->holder->name : ''))
                    ->visible(fn (
                        string $operation,
                        ?MemberRequest $record
                    ): bool => $record && $record->isOnHold()
                    )
                    ->schema([
                        Forms\Components\Section::make('On Hold Details')
                            ->description('Request placed on hold for the following reason:')
                            ->columns(3)
                            ->schema([
                                Forms\Components\Textarea::make('notes')
                                    ->hiddenLabel()
                                    ->rows(3)
                                    ->columnSpanFull()
                                    ->disabled(),
                            ]),
                    ]),

                Forms\Components\Wizard\Step::make('Approved')
                    ->visible(fn (
                        string $operation,
                        ?MemberRequest $record
                    ): bool => $record && $record->newQuery()->approved()->whereKey($record)->exists()
                    )
                    ->icon('heroicon-o-check-circle')
                    ->schema([
                        Forms\Components\Section::make('Approval Details')
                            ->columns(2)
                            ->schema([
                                Forms\Components\Placeholder::make('Approved')
                                    ->label('Approved At')
                                    ->content(fn (?Model $record): string => $record->approved_at?->toDayDateTimeString()
                                        . ' - '
                                        . $record->approved_at?->diffForHumans()),
                                Forms\Components\Placeholder::make('Approver')
                                    ->content(fn (?Model $record): string => $record
                                        ? $record->approver->present()->rankName
                                        : ''
                                    ),
                            ]),
                    ]),
            ])
                ->submitAction(false)
                ->startOnStep(function (string $operation, ?MemberRequest $record): int {

                    if ($record->isOnHold()) {
                        return 2;
                    }

                    if ($record->approved_at) {
                        return 2;
                    }

                    return 0;
                })
                ->skippable(false)
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('member.name')
                    ->label('Member')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('division.name')
                    ->label('Division')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('requester.name')
                    ->label('Requester')
                    ->toggleable()
                    ->toggledHiddenByDefault()
                    ->searchable(),
                Tables\Columns\TextColumn::make('approver.name')
                    ->label('Approver')
                    ->toggleable()
                    ->toggledHiddenByDefault()
                    ->default('-'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->label('Status')
                    ->getStateUsing(function (MemberRequest $record): string {
                        if ($record->isApproved()) {
                            return 'Approved';
                        }

                        $isOnHold = $record->isOnHold();

                        if ($isOnHold) {
                            return 'On Hold';
                        }

                        return 'Pending';
                    })
                    ->colors([
                        'warning' => 'Pending',
                        'success' => 'Approved',
                        'danger' => 'On Hold',
                    ]),
                Tables\Columns\TextColumn::make('approved_at')
                    ->label('Approved')
                    ->formatStateUsing(fn ($state) => Carbon::parse($state)->diffForHumans())
                    ->sortable()
                    ->toggleable()
                    ->tooltip(fn ($state) => Carbon::parse($state)->toDayDateTimeString()),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->sortable()
                    ->toggleable()
                    ->formatStateUsing(fn ($state) => Carbon::parse($state)->diffForHumans())
                    ->tooltip(fn ($state) => Carbon::parse($state)->toDayDateTimeString())
                    ->color(function ($state, $record) {
                        return ! $record->isApproved() &&
                        Carbon::parse($state)->lt(now()->subHours(2))
                            ? 'warning'
                            : null;
                    }),

            ])
            ->defaultSort('id', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->native(false)
                    ->default('pending')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'on_hold' => 'On Hold',
                    ])
                    ->query(function (Builder $query, array $data) {
                        return match ($data['value'] ?? null) {
                            'pending' => $query->pending(),
                            'approved' => $query->approved(),
                            'on_hold' => $query->onHold(),
                            default => $query,
                        };
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMemberRequests::route('/'),
            'edit' => Pages\EditMemberRequest::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        /** @var Builder $query */
        $query = parent::getEloquentQuery()->with(['member', 'requester', 'approver', 'division']);

        $user = auth()->user();

        if (self::isDivisionLeadership() && ! $user->isRole('admin')) {
            $divisionId = $user->member?->division_id;
            if ($divisionId) {
                $query->where('division_id', $divisionId);
            }
        }

        return $query;
    }

    private static function isDivisionLeadership(): bool
    {
        $user = auth()->user();

        $pos = $user->member?->position;

        return in_array($pos, [
            Position::EXECUTIVE_OFFICER,
            Position::COMMANDING_OFFICER,
        ], true);
    }
}
