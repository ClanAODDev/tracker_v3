<?php

namespace App\Filament\Mod\Resources\RankActionResource\Pages;

use App\Enums\Rank;
use App\Filament\Mod\Resources\RankActionResource;
use App\Jobs\UpdateRankForMember;
use App\Models\Member;
use App\Models\RankAction;
use App\Notifications\PromotionPendingAcceptance;
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
        if (!$member) {
            throw new \Exception('Member not found.');
        }

        if ($data['action'] === 'promotion') {
            $newRankValue = $member->rank->value + 1;

            try {
                $newRank = Rank::from($newRankValue);
            } catch (\ValueError $e) {
                $newRank = Rank::from($member->rank);
            }
            $data['rank'] = $newRank->value;
        } elseif ($data['action'] === 'demotion') {
            $data['rank'] = $data['demotion_rank'];
        }

        unset($data['action'], $data['demotion_rank']);

        $division = auth()->user()->division;

        $data['approved_at'] = Rank::autoApprovedTimestampForRank(
            $data['rank'],
            $division
        );

        if ($data['approved_at']) {
            $data['accepted_at'] = $data['approved_at'];
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        /** @var RankAction $record */
        $record = $this->record;

        if ($record->isApproved()) {
            try {
                UpdateRankForMember::dispatch($record);
            } catch (\Exception $exception) {
                \Log::error($exception->getMessage());
            }
        }
    }

    protected function getSteps(): array
    {
        return [
            Step::make('Select Member')
                ->schema([
                    RankActionResource::getMemberFormField(),
                ]),
            Step::make('Select Rank')
                ->schema(RankActionResource::getRankActionFields()),
            Step::make('Justification')
                ->schema([
                    RankActionResource::getJustificationFormField(),
                ]),
        ];
    }
}
