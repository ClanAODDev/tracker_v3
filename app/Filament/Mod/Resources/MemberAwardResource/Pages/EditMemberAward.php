<?php

namespace App\Filament\Mod\Resources\MemberAwardResource\Pages;

use App\Filament\Mod\Resources\MemberAwardResource;
use App\Models\MemberAward;
use App\Notifications\Channel\NotifyDivisionMemberAwarded;
use App\Notifications\DM\NotifyMemberAwardReceived;
use App\Notifications\DM\NotifyRequesterAwardDenied;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Pages\EditRecord;

class EditMemberAward extends EditRecord
{
    protected static string $resource = MemberAwardResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->requiresConfirmation()
                ->modalHeading('Deny Award')
                ->modalDescription('Are you sure you want to deny this award? The member will be notified.')
                ->form([
                    Textarea::make('reason')
                        ->label('Reason')
                        ->required(),
                ])
                ->label('Deny')
                ->before(function (array $data, MemberAward $memberAward) {
                    $memberAward->requester->notify(new NotifyRequesterAwardDenied(
                        $memberAward->award->name,
                        $data['reason']
                    ));
                }),

            Action::make('approve')
                ->label('Approve Award')
                ->action(fn (MemberAward $award) => $award->update(['approved' => true]))
                ->hidden(fn ($action) =>
                    // already approved
                    $action->getRecord()->approved
                )
                ->requiresConfirmation()
                ->modalHeading('Approve Award')
                ->modalDescription('Are you sure you want to approve this award?')
                ->after(function (MemberAward $memberAward) {
                    if ($memberAward->member->division->settings()->get('chat_alerts.member_awarded')) {
                        $memberAward->member->division->notify(new MemberAwarded(
                            $memberAward->member->name,
                            $memberAward->award
                        ));
                    }
                }),
        ];
    }
}
