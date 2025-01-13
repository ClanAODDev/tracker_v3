<?php

namespace App\Filament\Admin\Resources\LeaveResource\Pages;

use App\Filament\Admin\Resources\LeaveResource;
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
            Actions\DeleteAction::make(),
            Action::make('approve')
                ->label('Approve Leave')
                ->action(fn (Leave $leave) => $leave->update(['approver_id' => auth()->id()]))
                ->hidden(fn ($action) => $action->getRecord()->approver_id)
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
