<?php

namespace App\Filament\Mod\Resources\RankActionResource\Pages;

use App\Enums\Rank;
use App\Filament\Mod\Resources\RankActionResource;
use App\Jobs\UpdateRankForMember;
use App\Models\Member;
use App\Models\RankAction;
use App\Models\User;
use App\Notifications\Channel\NotifyAdminSgtRequestPending;
use App\Notifications\DM\NotifyMemberPromotionPendingAcceptance;
use Exception;
use Filament\Forms\Components\RichEditor;
use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Pages\CreateRecord\Concerns\HasWizard;
use Filament\Schemas\Components\Wizard\Step;
use ValueError;

class CreateRankAction extends CreateRecord
{
    use HasWizard;

    protected static string $resource = RankActionResource::class;

    public function mutateFormDataBeforeCreate(array $data): array
    {
        $data['requester_id'] = auth()->user()->member_id;

        $member = Member::find($data['member_id']);
        if (! $member) {
            throw new Exception('Member not found.');
        }

        if ($data['action'] === 'promotion') {
            if (isset($data['promotion_rank']) && auth()->user()->isDivisionLeader()) {
                $data['rank'] = $data['promotion_rank'];
            } else {
                $newRankValue = $member->rank->value + 1;
                try {
                    $newRank = Rank::from($newRankValue);
                } catch (ValueError $e) {
                    $newRank = $member->rank;
                }
                $data['rank'] = $newRank->value;
            }
        } elseif ($data['action'] === 'demotion') {
            $data['rank'] = $data['demotion_rank'];
            $data['accepted_at'] = now();
        }

        unset($data['action'], $data['demotion_rank'], $data['promotion_rank'], $data['override_existing']);

        $division = auth()->user()->division;

        $data['approved_at'] = User::autoApprovedTimestampForRank(
            $data['rank'],
            $division
        );

        return $data;

    }

    protected function afterCreate(): void
    {
        /** @var RankAction $record */
        $record = $this->record;

        // fast track promotions up to platoon lead limit (Cdt <-> PFC)
        if (auth()->user()->canApproveOrDeny($record)) {
            $record->approveAndAccept();
            UpdateRankForMember::dispatch($record);
        }

        // notify admins if a Sergeant rank request is pending
        if (
            $record->rank->value >= Rank::SERGEANT->value &&
            $record->rank->value < Rank::FIRST_SERGEANT->value &&
            $record->rank->isPromotion($record->member->rank)
        ) {
            $userName = auth()->check() ? auth()->user()->name : 'System';

            $record->rank->notify(new NotifyAdminSgtRequestPending(
                $userName,
                $record->member->name,
                $record->rank->getLabel(),
                $record->id
            ));
        }

        // if additional approval is needed, stop here
        if (! $record->isApproved()) {
            return;
        }

        // promotions beyond platoon lead limit (>= SPC) require acceptance
        if ($record->rank->isPromotion($record->member->rank) && ! $record->accepted_at) {
            $record->member->notify(new NotifyMemberPromotionPendingAcceptance($record));
        } else {
            // demotions are automatically accepted and applied
            UpdateRankForMember::dispatch($record);
        }
    }

    protected function getSteps(): array
    {
        return [
            Step::make('Select Member')
                ->schema(RankActionResource::getMemberFormFields()),
            Step::make('Select Rank')
                ->schema(RankActionResource::getRankActionFields()),
            Step::make('Justification')
                ->schema([
                    RichEditor::make('Justification')
                        ->required()
                        ->columnSpanFull(),
                ]),
        ];
    }
}
