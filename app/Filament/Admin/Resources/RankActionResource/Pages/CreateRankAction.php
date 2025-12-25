<?php

namespace App\Filament\Admin\Resources\RankActionResource\Pages;

use App\Filament\Admin\Resources\RankActionResource;
use App\Jobs\UpdateRankForMember;
use App\Models\RankAction;
use Exception;
use Filament\Resources\Pages\CreateRecord;
use Log;

class CreateRankAction extends CreateRecord
{
    protected static string $resource = RankActionResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['approved_at'] = now();
        $data['accepted_at'] = now();

        return $data;
    }

    protected function afterCreate(): void
    {
        /** @var RankAction $record */
        $record = $this->record;

        try {
            UpdateRankForMember::dispatch($record);
        } catch (Exception $exception) {
            Log::error($exception->getMessage());
        }
    }
}
