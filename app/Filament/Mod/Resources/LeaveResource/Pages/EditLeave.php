<?php

namespace App\Filament\Mod\Resources\LeaveResource\Pages;

use App\Filament\Mod\Resources\LeaveResource;
use App\Models\Leave;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;

class EditLeave extends EditRecord
{
    protected static string $resource = LeaveResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()->label('Revoke'),

            Action::make('approve')
                ->label('Approve Leave')
                ->action(fn (Leave $leave) => $leave->update(['approver_id' => auth()->id()]))
                ->hidden(fn ($action) =>
                        // already approved
                    $action->getRecord()->approver_id
                        // leave request for the current user
                    || $action->getRecord()->member_id === auth()->user()->member->clan_id
                )
                ->requiresConfirmation()
                ->modalHeading('Approve Leave')
                ->modalDescription('Are you sure you want to approve this leave request?'),

            Action::make('disapprove')
                ->label('Disapprove Leave')
                ->color('danger')
                ->action(fn (Leave $leave) => $leave->update(['approver_id' => null]))
                ->hidden(fn ($action) => ! $action->getRecord()->approver_id)
                ->requiresConfirmation()
                ->modalIconColor('warning')
                ->modalHeading('Disapprove Leave')
                ->modalDescription('Are you sure you want to disapprove this leave request?'),

        ];
    }
}
