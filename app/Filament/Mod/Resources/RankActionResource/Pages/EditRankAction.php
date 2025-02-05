<?php

namespace App\Filament\Mod\Resources\RankActionResource\Pages;

use App\Filament\Mod\Resources\RankActionResource;
use App\Jobs\UpdateRankForMember;
use App\Models\RankAction;
use App\Notifications\DM\NotifyMemberPromotionPendingAcceptance;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;
use Parallax\FilamentComments\Actions\CommentsAction;

class EditRankAction extends EditRecord
{
    protected static string $resource = RankActionResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        unset($data['status'], $data['rank']);

        return $data;
    }

    protected function getHeaderActions(): array
    {
        $commentsAction = [CommentsAction::make()];

        $actions = [
            Actions\DeleteAction::make()->label('Deny')
                ->visible(fn (RankAction $action) => auth()->user()->canApproveOrDeny($action))
                ->hidden(fn ($action) => $action->getRecord()->approved_at),

            Action::make('requeue')
                ->label('Requeue Acceptance')
                ->color('info')
                // visible only if rank is below user current
                ->visible(fn ($action) => auth()->user()->isDivisionLeader() || auth()->user()->isRole('admin'))
                ->hidden(fn ($action) => ($record = $action->getRecord()) && (
                    $record->resolved()
                    || ! $record->rank->isPromotion($record->member->rank)
                    || ! $record->approved_at
                    || $record->approved_at?->gt(now()->subMinutes(
                        config('app.aod.rank.promotion_acceptance_mins')
                    ))
                ))
                ->requiresConfirmation()
                ->modalHeading('Requeue Acceptance')
                ->modalDescription('Confirm that you wish to send the user a new promotion acceptance message')
                ->after(function (RankAction $action) {
                    try {
                        if ($action->rank->isPromotion($action->member->rank)) {
                            $action->member->notify(new NotifyMemberPromotionPendingAcceptance($action));
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
                        $action->member->notify(new NotifyMemberPromotionPendingAcceptance($action));
                    } else {
                        $action->approveAndAccept();
                        UpdateRankForMember::dispatch($action);
                    }
                })
                ->visible(fn (RankAction $action) => auth()->user()->canApproveOrDeny($action))
                ->hidden(fn ($action) => $action->getRecord()->approved_at)
                ->requiresConfirmation()
                ->modalHeading('Approve Rank Change')
                ->modalDescription('Are you sure you want to approve this rank change?'),
        ];

        return auth()->user()->canApproveOrDeny($this->getRecord())
            ? array_merge($actions, $commentsAction)
            : $actions;
    }
}
