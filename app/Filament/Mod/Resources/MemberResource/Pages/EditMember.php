<?php

namespace App\Filament\Mod\Resources\MemberResource\Pages;

use App\Filament\Forms\Components\IngameHandlesForm;
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

class EditMember extends EditRecord
{
    protected static string $resource = MemberResource::class;

    public function getTitle(): string
    {
        return $this->record->division_id
            ? $this->record->name
            : sprintf('[Ex-AOD] %s', $this->record->name);
    }

    public function mutateFormDataBeforeFill(array $data): array
    {
        if (isset($data['id'])) {
            $member = \App\Models\Member::find($data['id']);
            if ($member) {
                $data['handleGroups'] = IngameHandlesForm::getGroupedHandles($member);
            }
        }

        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        if ($record->isDirty('last_trained_by')) {
            $data['last_trained_at'] = now();
        }

        IngameHandlesForm::saveHandles($record, $data['handleGroups']);

        unset($data['handleGroups']);

        $record->update($data);

        return $record;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('view_profile')
                ->label('View Profile')
                ->outlined()
                ->icon('heroicon-o-eye')
                ->url(fn ($record) => route('member', $record->getUrlParams()))
                ->openUrlInNewTab(),

            ActionGroup::make([

                Action::make('trigger_external_removal')
                    ->label('Step 1: Forum removal')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn () => sprintf(
                        'https://www.clanaod.net/forums/modcp/aodmember.php?do=remaod&u=%s',
                        $this->record->clan_id
                    ))
                    ->openUrlInNewTab()
                    ->color('danger'),

                Action::make('remove_member')
                    ->label('Step 2: Tracker removal')
                    ->icon('heroicon-o-trash')
                    ->color('warning')
                    ->modalDescription('This will only remove the member from the tracker, and reset any division, squad, or platoon assignments. Please provide a reason for the removal. Note: If you have not done forum removal, the member will be restored on the next sync.')
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
