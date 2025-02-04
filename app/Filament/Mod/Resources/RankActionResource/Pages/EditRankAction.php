<?php

namespace App\Filament\Mod\Resources\RankActionResource\Pages;

use App\Filament\Mod\Resources\RankActionResource;
use App\Jobs\UpdateRankForMember;
use App\Models\RankAction;
use App\Notifications\PromotionPendingAcceptance;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;
use Parallax\FilamentComments\Actions\CommentsAction;

class EditRankAction extends EditRecord
{
    protected static string $resource = RankActionResource::class;

    protected function getHeaderActions(): array
    {


        return [
            CommentsAction::make(),

            Actions\DeleteAction::make()->label('Deny')
                ->hidden(fn($action) => $action->getRecord()->approved_at),

            Action::make('requeue')
                ->label('Requeue Acceptance')
                ->color('info')
                // visible only if rank is below user current
                ->visible(fn($action) => auth()->user()->isDivisionLeader() || auth()->user()->isRole('admin'))
                // hidden only if both approved and accepted are set - allows re-queue of temporary accept step
                ->hidden(fn($action) => tap($action->getRecord(), function ($record) {
                    return $record->accepted_at
                        || !$record->rank->isPromotion($record->member->rank)
                        || !$record->approved_at
                        || $record->approved_at?->lt(now()->addMinutes(10));
                }))
                ->requiresConfirmation()
                ->modalHeading('Requeue Acceptance')
                ->modalDescription('Confirm that you wish to send the user a new promotion acceptance message')
                ->after(function (RankAction $action) {
                    try {
                        if ($action->rank->isPromotion($action->member->rank)) {
                            $action->member->notify(new PromotionPendingAcceptance($action));
                        }
                    } catch (\Exception $e) {
                        \Log::error($e->getMessage());
                    }
                }),

            Action::make('approve')
                ->label('Approve')
                ->action(function (RankAction $action) {
                    if ($action->rank->isPromotion($action->member->rank)) {
                        $action->approve();
                        $action->member->notify(new PromotionPendingAcceptance($action));
                    } else {
                        $action->approveAndAccept();
                        UpdateRankForMember::dispatch($action);
                    }
                })

                ->visible(function (RankAction $action) {
                    $user = auth()->user();
                    $newRank = $action->rank;

                    if ($newRank->value >= Rank::SERGEANT->value) {
                        // MSGT+ approval authority on SGT+ promotions
                        return $user->member->rank->value >= Rank::MASTER_SERGEANT->value;
                    }

                    return $user->isDivisionLeader() || $user->isRole('admin');
                })

                // hidden only if both approved and accepted are set - allows re-queue of temporary accept step
                ->hidden(fn($action) => $action->getRecord()->approved_at)
                ->requiresConfirmation()
                ->modalHeading('Approve Rank Change')
                ->modalDescription('Are you sure you want to approve this rank change?'),
        ];
    }
}
