<?php

namespace App\Filament\Admin\Resources\MemberResource\Pages;

use App\Console\Commands\FixTenureAwards;
use App\Filament\Actions\Members\CleanupTenureAwards;
use App\Filament\Actions\Members\CleanupUnassignedLeadersAction;
use App\Filament\Actions\Members\PartTimeMemberCleanupAction;
use App\Filament\Admin\Resources\MemberResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\ActionSize;

class ListMembers extends ListRecords
{
    protected static string $resource = MemberResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),

            Actions\ActionGroup::make([
                CleanupTenureAwards::make(),
                CleanupUnassignedLeadersAction::make(),
                PartTimeMemberCleanupAction::make(),
            ])->label('Cleanup Actions')
                ->color('primary')
                ->button()
                ->icon('heroicon-o-wrench-screwdriver'),
        ];
    }
}
