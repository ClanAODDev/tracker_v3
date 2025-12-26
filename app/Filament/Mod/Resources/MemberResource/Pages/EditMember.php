<?php

namespace App\Filament\Mod\Resources\MemberResource\Pages;

use App\Filament\Forms\Components\IngameHandlesForm;
use App\Filament\Forms\Components\PartTimeDivisionsForm;
use App\Filament\Mod\Resources\MemberResource;
use App\Jobs\RemoveClanMember;
use App\Models\DivisionTag;
use App\Models\Member;
use App\Models\Note;
use App\Notifications\Channel\NotifyDivisionMemberRemoved;
use App\Notifications\Channel\NotifyDivisionPartTimeMemberRemoved;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
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
            $member = Member::find($data['id']);
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

        PartTimeDivisionsForm::sync(
            $this->record,
            PartTimeDivisionsForm::selectedFrom($this->data ?? [])
        );

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

            Action::make('remove_member')
                ->label('Remove Member')
                ->icon('heroicon-o-trash')
                ->color('danger')
                ->modalDescription('This will remove the member from the clan and reset their position and assignments. This action cannot be undone.')
                ->schema([
                    Textarea::make('removal_reason')
                        ->label('Reason for Removal')
                        ->required(),
                    Select::make('global_tag_id')
                        ->label('Apply Clan-Wide Tag')
                        ->helperText('Optionally tag this member before removal (e.g., "Do Not Recruit")')
                        ->options(fn () => DivisionTag::global()
                            ->visibleTo()
                            ->orderBy('name')
                            ->pluck('name', 'id'))
                        ->searchable()
                        ->preload(),
                ])
                ->hidden(fn (): bool => ! $this->record->division_id)
                ->action(function (array $data) {
                    $member = $this->record;

                    if (! empty($data['global_tag_id'])) {
                        $member->tags()->syncWithoutDetaching([
                            $data['global_tag_id'] => ['assigned_by' => auth()->user()->member?->id],
                        ]);
                    }

                    Note::create([
                        'type' => 'negative',
                        'body' => 'Member removal: ' . $data['removal_reason'],
                        'author_id' => auth()->id(),
                        'member_id' => $member->id,
                    ]);

                    $this->notifyDivisions($member, $data['removal_reason']);

                    RemoveClanMember::dispatch(
                        impersonatingMemberId: auth()->user()->member->clan_id,
                        memberIdBeingRemoved: $member->clan_id,
                    );

                    $member->reset();

                    Notification::make()
                        ->title('Member Removed')
                        ->body($data['removal_reason'] ?? 'Member removed successfully')
                        ->success()
                        ->send();
                }),
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
            $division->notify(new NotifyDivisionPartTimeMemberRemoved($member, $reason));
        }
    }
}
