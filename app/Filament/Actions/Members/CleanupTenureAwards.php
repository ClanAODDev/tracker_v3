<?php

namespace App\Filament\Actions\Members;

use App\Models\Member;
use App\Models\MemberAward;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\DB;

class CleanupTenureAwards
{
    private const TENURE_AWARD_IDS = [
        20 => 140,
        15 => 139,
        10 => 19,
        5  => 18,
    ];

    /**
     * A header action for use in ListMembers::getHeaderActions()
     */
    public static function make(string $name = 'fixTenureAwards'): Action
    {
        return Action::make($name)
            ->label('Fix Tenure Awards')
            ->schema([
                Toggle::make('persist')
                    ->label('Persist changes')
                    ->helperText('If off, runs a dry-run and only reports the counts.')
                    ->default(false),
            ])
            ->requiresConfirmation()
            ->action(function (array $data, $livewire) {
                /** @var ListRecords $livewire */
                $query = method_exists($livewire, 'getFilteredTableQuery')
                    ? $livewire->getFilteredTableQuery()
                    : Member::query();

                $members = $query->whereHas('division')->get();

                $summary = DB::transaction(function () use ($members, $data) {
                    return self::runFixForMembers($members, (bool) ($data['persist'] ?? false));
                }, 3);

                self::notifySummary($summary, (bool) ($data['persist'] ?? false));
            });
    }

    private static function runFixForMembers($members, bool $persist): array
    {
        $missing = 0;
        $invalid = 0;

        foreach ($members as $member) {
            $years = Carbon::parse($member->join_date)->diffInYears(now());

            $missing += self::processMissingAwards($member, $years, $persist);
            $invalid += self::processInvalidAwards($member, $years, $persist);
        }

        return compact('missing', 'invalid');
    }

    private static function eligibleMilestones(int $years, Carbon $joinDate): array
    {
        $earned = array_filter(
            self::TENURE_AWARD_IDS,
            fn ($_, $milestone) => $years >= $milestone,
            ARRAY_FILTER_USE_BOTH,
        );

        if ($joinDate->month === now()->month) {
            foreach (self::TENURE_AWARD_IDS as $milestone => $awardId) {
                if ($years + 1 >= $milestone && ! isset($earned[$milestone])) {
                    $earned[$milestone] = $awardId;
                }
            }
        }

        return $earned;
    }

    private static function processMissingAwards($member, int $years, bool $persist): int
    {
        $count    = 0;
        $joinDate = Carbon::parse($member->join_date);

        foreach (self::eligibleMilestones($years, $joinDate) as $milestone => $awardId) {
            $has = MemberAward::where('member_id', $member->clan_id)
                ->where('award_id', $awardId)
                ->exists();

            if (! $has) {
                if ($persist) {
                    MemberAward::firstOrCreate(
                        ['member_id' => $member->clan_id, 'award_id' => $awardId],
                        ['reason' => "Awarded for reaching the {$milestone}-year milestone.", 'approved' => true],
                    );
                }
                $count++;
            }
        }

        return $count;
    }

    private static function processInvalidAwards($member, int $years, bool $persist): int
    {
        $count    = 0;
        $joinDate = Carbon::parse($member->join_date);
        $eligible = self::eligibleMilestones($years, $joinDate);

        $awards = MemberAward::where('member_id', $member->clan_id)
            ->whereIn('award_id', array_values(self::TENURE_AWARD_IDS))
            ->get();

        foreach ($awards as $award) {
            if (! in_array($award->award_id, $eligible, strict: true)) {
                if ($persist) {
                    $award->delete();
                }
                $count++;
            }
        }

        return $count;
    }

    private static function notifySummary(array $summary, bool $persist): void
    {
        $mode = $persist ? 'Applied' : 'Dry-run';
        $body = implode('<br />', [
            "{$mode} tenure award corrections:",
            '- Missing awards ' . ($persist ? 'added' : 'found') . ": {$summary['missing']}",
            '- Invalid awards ' . ($persist ? 'removed' : 'found') . ": {$summary['invalid']}",
        ]);

        Notification::make()
            ->title('Tenure Awards – Summary')
            ->body($body)
            ->success()
            ->persistent()
            ->send();
    }
}
