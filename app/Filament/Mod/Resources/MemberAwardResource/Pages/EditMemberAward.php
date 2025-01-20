<?php

namespace App\Filament\Mod\Resources\MemberAwardResource\Pages;

use App\Filament\Mod\Resources\MemberAwardResource;
use App\Models\MemberAward;
use App\Notifications\MemberAwarded;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;

class EditMemberAward extends EditRecord
{
    protected static string $resource = MemberAwardResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),

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
