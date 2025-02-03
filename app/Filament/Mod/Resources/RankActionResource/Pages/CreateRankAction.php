<?php

namespace App\Filament\Mod\Resources\RankActionResource\Pages;

use App\Enums\Rank;
use App\Filament\Mod\Resources\RankActionResource;
use Filament\Forms\Components\Wizard\Step;
use Filament\Resources\Pages\CreateRecord;

class CreateRankAction extends CreateRecord
{
    use CreateRecord\Concerns\HasWizard;

    protected static string $resource = RankActionResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['requester_id'] = auth()->user()->member_id;

        $division = auth()->user()->division;
        $data['approved_at'] = Rank::autoApprovedTimestampForRank($data['rank'], $division);

        return $data;
    }

    protected function getSteps(): array
    {
        return [
            Step::make('Select Member')
                ->schema([
                    RankActionResource::getMemberFormField(),
                ]),
            Step::make('Select Rank')
                ->schema([
                    RankActionResource::getRankFormField(),
                ]),
            Step::make('Justification')
                ->schema([
                    RankActionResource::getJustificationFormField(),
                ]),
        ];
    }
}
