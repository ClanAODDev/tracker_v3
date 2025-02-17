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
use Filament\Forms\Components\Wizard\Step;
use Filament\Resources\Pages\CreateRecord;

class CreateRankAction extends CreateRecord
{
    use CreateRecord\Concerns\HasWizard;

    protected static string $resource = RankActionResource::class;

    public function mutateFormDataBeforeCreate(array $data): array
    {
        $data['requester_id'] = auth()->user()->member_id;

        $member = Member::find($data['member_id']);
        if (! $member) {
            throw new \Exception('Member not found.');
        }

        if ($data['action'] === 'promotion') {
            if (isset($data['promotion_rank']) && auth()->user()->isDivisionLeader()) {
                $data['rank'] = $data['promotion_rank'];
            } else {
                $newRankValue = $member->rank->value + 1;
                try {
                    $newRank = Rank::from($newRankValue);
                } catch (\ValueError $e) {
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

        if ($record->rank->value > Rank::SERGEANT->value && $record->rank->isPromotion($record->member->rank)) {
            $record->rank->notify(new NotifyAdminSgtRequestPending(
                auth()->user()->name,
                $record->member->name,
                $record->rank->getLabel(),
                $record->id
            ));
        }

        if ($record->isApproved()) {
            if ($record->rank->isPromotion($record->member->rank)) {
                $record->member->notify(new NotifyMemberPromotionPendingAcceptance($record));
            } else {
                UpdateRankForMember::dispatch($record);
            }
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
                    RankActionResource::getJustificationFormField(),
                ]),
        ];
    }
}
