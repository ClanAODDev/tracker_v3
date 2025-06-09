<?php

namespace App\Filament\Mod\Resources\TransferResource\Pages;

use App\Filament\Mod\Resources\TransferResource;
use App\Models\Division;
use App\Models\Member;
use App\Models\Transfer;
use App\Notifications\Channel\NotifyDivisionMemberTransferRequested;
use Closure;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Wizard\Step;
use Filament\Forms\Get;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Builder;

class CreateTransfer extends CreateRecord
{
    use CreateRecord\Concerns\HasWizard;

    protected static string $resource = TransferResource::class;

    protected static ?string $title = 'Create Division Transfer';

    protected function afterCreate(): void
    {
        $transfer = $this->record;

        $member = $transfer->member;
        $oldDivision = $member->division;
        $newDivision = $transfer->division;
        $newDivName = $newDivision->name;

        $notifications = [
            [$oldDivision, 'OUTGOING'],
            [$newDivision, 'INCOMING'],
        ];

        foreach ($notifications as [$division, $type]) {
            $this->sendTransferNotification($division, $member, $newDivName, $type);
        }
    }

    private function sendTransferNotification(
        Division $division,
        Member $member,
        string $newDivisionName,
        string $type,
    ): void {
        $division->notify(new NotifyDivisionMemberTransferRequested($member, $newDivisionName, $type));
    }

    public function getSteps(): array
    {
        return [
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
                        ->afterStateUpdated(function ($state, callable $set, Get $get) {
                            $memberId = $get('member_id');

                            if ($memberId) {
                                $member = Member::find($memberId);
                                $set('current_division', $member ? $member->division->name : 'N/A');
                            } else {
                                $set('current_division', 'N/A');
                            }
                        }),

                    Placeholder::make('current_division')
                        ->label('Transferring From')
                        ->visible(fn (Get $get) => $get('current_division') !== null)
                        ->content(fn (Get $get) => $get('current_division')
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
                            modifyQueryUsing: fn (Builder $query, Get $get) => $query
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

        ];
    }
}
