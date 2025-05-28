<?php

namespace App\Filament\Mod\Resources\MemberResource\Pages;

use App\Filament\Mod\Resources\MemberResource;
use App\Models\Member;
use App\Models\Note;
use App\Notifications\Channel\NotifydDivisionPartTimeMemberRemoved;
use App\Notifications\Channel\NotifyDivisionMemberRemoved;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Livewire\Component;

class EditMember extends EditRecord
{
    protected static string $resource = MemberResource::class;

    public function getTitle(): string
    {
        return $this->record->division_id
            ? $this->record->name
            : sprintf('[Ex-AOD] %s', $this->record->name);
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        if ($record->isDirty('last_trained_by')) {
            $data['last_trained_at'] = now();
        }

        $record->update($data);

        return $record;
    }

    protected function getHeaderActions(): array
    {
        return [
            //            Actions\DeleteAction::make(),
            Action::make('view_profile')
                ->label('View Profile')
                ->outlined()
                ->icon('heroicon-o-eye')
                ->url(fn ($record) => route('member', $record->getUrlParams()))
                ->openUrlInNewTab(),

            ActionGroup::make([

                Action::make('trigger_external_removal')
                    ->label(fn (Component $livewire
                    ) => $livewire->state['external_removal_done'] ?? false ? 'External Removal Initiated' : 'Remove from forums')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn () => sprintf(
                        'https://www.clanaod.net/forums/modcp/aodmember.php?do=remaod&u=%s',
                        $this->record->clan_id
                    ))
                    ->openUrlInNewTab()
                    ->color('danger'),

                Action::make('remove_member')
                    ->label('Complete Removal')
                    ->icon('heroicon-o-trash')
                    ->color('warning')
                    ->modalDescription('This will remove the member from full and part-time divisions, squad, and platoon. Please provide a reason for the removal. If forum removal was completed, the sync process will remove the member from the tracker.')
                    ->form([
                        Textarea::make('removal_reason')
                            ->label('Reason for Removal')
                            ->required(),
                    ])
                    ->action(function (Member $member, array $data) {

                        Note::create([
                            'type' => 'negative',
                            'body' => 'Member removal:' . $data['removal_reason'],
                            'author_id' => auth()->id(),
                            'member_id' => $member->id,
                        ]);

                        $this->notifyDivisions($member, $data['removal_reason']);

                        $member->resetPositionAndAssignments();

                        Notification::make()
                            ->title('Member Removed')
                            ->body($data['removal_reason'] ?? 'Member removed successfully')
                            ->success()
                            ->send();
                    }),

            ])->label('Remove...')
                ->icon('heroicon-m-trash')
                ->color('danger')
                ->visible(fn () => auth()->user()->can('separate', $this->record) && $this->record->division_id)
                ->button(),

        ];
    }

    private function notifyDivisions(Member $member, ?string $reason = null): void
    {
        if ($member->division()->exists()) {
            $member->division->notify(new NotifyDivisionMemberRemoved($member, auth()->user(), $reason, $member->squad)
            );
        }

        $divisions = $member->partTimeDivisions()->active()->get();

        foreach ($divisions as $division) {
            $division->notify(new NotifydDivisionPartTimeMemberRemoved($member, $reason));
        }
    }
}
