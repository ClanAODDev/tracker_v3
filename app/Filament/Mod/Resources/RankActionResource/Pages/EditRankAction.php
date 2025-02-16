<?php

namespace App\Filament\Mod\Resources\RankActionResource\Pages;

use App\Enums\Rank;
use App\Filament\Mod\Resources\RankActionResource;
use App\Jobs\UpdateRankForMember;
use App\Models\RankAction;
use App\Notifications\DM\NotifyMemberPromotionPendingAcceptance;
use App\Notifications\DM\NotifyRequesterRankActionDenied;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Pages\EditRecord;
use Parallax\FilamentComments\Actions\CommentsAction;

class EditRankAction extends EditRecord
{
    protected static string $resource = RankActionResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        unset($data['status'], $data['type']);

        return $data;
    }

    protected function getHeaderActions(): array
    {
        $commentsAction = [CommentsAction::make()];

        $actions = [
            Actions\Action::make('deny')->label('Deny change')
                ->color('danger')
                ->visible(fn (RankAction $action) => auth()->user()->canApproveOrDeny($action))
                ->hidden(fn ($action) => ! $action->getRecord()->actionable())
                ->requiresConfirmation()
                ->modalHeading('Deny Rank Action')
                ->modalDescription('Are you sure you want to deny this rank action? The requester will be notified.')
                ->form([
                    Textarea::make('deny_reason')
                        ->label('Reason')
                        ->required(),
                ])
                ->action(function (array $data, RankAction $action) {
                    $action->deny($data['deny_reason']);
                })
                ->before(function (array $data, RankAction $rankAction) {
                    $rankAction->requester->notify(new NotifyRequesterRankActionDenied(
                        $rankAction,
                        $data['deny_reason']
                    ));
                }),

            Action::make('requeue')
                ->label('Requeue Acceptance')
                ->color('info')
                ->visible(fn ($action) => auth()->user()->isDivisionLeader() || auth()->user()->isRole('admin'))
                ->hidden(fn ($action) => ($record = $action->getRecord()) && (
                    ! $record->rank->isPromotion($record->member->rank)
                    || ! $record->approved_at
                    || $record->approved_at->gt(now()->subMinutes(
                        config('app.aod.rank.promotion_acceptance_mins')
                    ))
                    || $record->accepted_at
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
                ->label('Approve change')
                ->closeModalByClickingAway(false)
                ->form([
                    Radio::make('notification_type')
                        ->label('Rank Acceptance Notification')
                        ->options([
                            'now' => 'Send Now',
                            'later' => 'Schedule for Later',
                        ])
                        ->default('now')
                        ->live(),
                    DateTimePicker::make('scheduled_at')
                        ->label('Schedule Notification')
                        ->minDate(now())
                        ->helperText('Timezone: America/New_York')
                        ->required()
                        ->hidden(fn ($get) => $get('notification_type') !== 'later'),
                ])
                ->action(function (array $data, RankAction $action) {
                    if ($action->rank->isPromotion($action->member->rank)) {
                        $action->approve();

                        $notification = new NotifyMemberPromotionPendingAcceptance($action);
                        if ($data['notification_type'] === 'later') {
                            $scheduledTime = \Carbon\Carbon::parse($data['scheduled_at']);
                            $action->member->notify($notification->delay($scheduledTime));
                        } else {
                            $action->member->notify($notification);
                        }
                    } else {
                        $action->approveAndAccept();
                        UpdateRankForMember::dispatch($action);
                    }
                })
                ->visible(fn (RankAction $action) => auth()->user()->canApproveOrDeny($action))
                ->hidden(fn ($action) => ! $action->getRecord()->actionable())
                ->requiresConfirmation()
                ->modalHeading('Approve Rank Change')
                ->modalDescription('Member will receive a notification prompting them to accept or decline the rank change'),

            Action::make('approve_with_force')
                ->color('warning')
                ->requiresConfirmation()
                ->visible(
                    fn (RankAction $action) => auth()->user()->canApproveOrDeny($action)
                        && $action->rank->value <= Rank::PRIVATE_FIRST_CLASS->value
                )
                ->hidden(fn (RankAction $action) => $action->accepted_at && ! $action->actionable())
                ->action(function (RankAction $action) {
                    $action->approveAndAccept();
                    UpdateRankForMember::dispatch($action);
                })
                ->modalHeading('Approve Rank Change With Force')
                ->modalDescription('Apply rank change without prompting the user for acceptance'),
        ];

        return auth()->user()->canManageCommentsFor($this->getRecord())
            ? array_merge($actions, $commentsAction)
            : $actions;
    }
}
