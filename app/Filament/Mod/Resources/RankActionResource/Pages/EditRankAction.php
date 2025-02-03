<?php

namespace App\Filament\Mod\Resources\RankActionResource\Pages;

use App\Filament\Mod\Resources\RankActionResource;
use App\Models\MemberAward;
use App\Models\RankAction;
use App\Notifications\PromotionPendingAcceptance;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;

class EditRankAction extends EditRecord
{
    protected static string $resource = RankActionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()->label('Deny'),

            Action::make('approve')
                ->label('Approve Rank Change')
                ->action(fn (MemberAward $award) => $award->update(['approved' => true]))
                ->visible(fn ($action) => ! $action->getRecord()->approved_at || auth()->user()
                    ->isDivisionLeader() || auth()->user()->isRole('admin'))
                ->requiresConfirmation()
                ->modalHeading('Approve Rank Change')
                ->modalDescription('Are you sure you want to approve this rank change?')
                ->after(function (RankAction $action) {
                    try {
                        $action->member->notify(new PromotionPendingAcceptance($action));
                    } catch (\Exception $e) {
                        \Log::error($e->getMessage());
                    }
                }),
        ];
    }
}
