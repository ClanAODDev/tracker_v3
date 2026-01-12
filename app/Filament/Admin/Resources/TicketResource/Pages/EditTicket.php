<?php

namespace App\Filament\Admin\Resources\TicketResource\Pages;

use App\Enums\Role;
use App\Filament\Admin\Resources\TicketResource;
use App\Models\User;
use App\Notifications\DM\NotifyCallerTicketUpdated;
use App\Notifications\React\TicketReaction;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditTicket extends EditRecord
{
    protected static string $resource = TicketResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('assignToMe')
                ->label('Assign to Me')
                ->icon('heroicon-o-user')
                ->color('info')
                ->visible(fn () => $this->record->owner_id !== auth()->id() && ! $this->record->isResolved())
                ->action(function () {
                    $this->record->ownTo(auth()->user());
                    $this->record->notify(new TicketReaction('assigned'));

                    Notification::make()
                        ->title('Ticket assigned to you')
                        ->success()
                        ->send();

                    $this->refreshFormData(['state', 'owner_id']);
                }),

            Action::make('resolve')
                ->label('Resolve')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(fn () => ! $this->record->isResolved())
                ->requiresConfirmation()
                ->modalHeading('Resolve Ticket')
                ->modalDescription('Are you sure you want to mark this ticket as resolved?')
                ->action(function () {
                    $this->record->resolve();
                    $this->record->notify(new TicketReaction('resolved'));
                    $this->record->caller->notify(new NotifyCallerTicketUpdated($this->record, 'Your ticket has been resolved.'));

                    Notification::make()
                        ->title('Ticket resolved')
                        ->success()
                        ->send();

                    $this->refreshFormData(['state', 'resolved_at', 'owner_id']);
                }),

            Action::make('reject')
                ->label('Reject')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->visible(fn () => ! $this->record->isResolved())
                ->form([
                    Textarea::make('reason')
                        ->label('Rejection Reason')
                        ->required()
                        ->minLength(10)
                        ->rows(3),
                ])
                ->action(function (array $data) {
                    $this->record->reject();
                    $this->record->say("Rejected: {$data['reason']}");
                    $this->record->notify(new TicketReaction('rejected'));
                    $this->record->caller->notify(new NotifyCallerTicketUpdated($this->record, "Your ticket was rejected: {$data['reason']}"));

                    Notification::make()
                        ->title('Ticket rejected')
                        ->success()
                        ->send();

                    $this->refreshFormData(['state', 'resolved_at', 'owner_id']);
                }),

            Action::make('reopen')
                ->label('Reopen')
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->visible(fn () => $this->record->isResolved())
                ->requiresConfirmation()
                ->action(function () {
                    $this->record->reopen();

                    Notification::make()
                        ->title('Ticket reopened')
                        ->success()
                        ->send();

                    $this->refreshFormData(['state', 'resolved_at']);
                }),

            DeleteAction::make(),
        ];
    }
}
