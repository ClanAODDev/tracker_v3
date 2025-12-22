<?php

namespace App\Filament\Actions\Members;

use App\Models\Member;
use App\Models\MemberAward;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;

class CleanupTenureAwards
{
    private const TENURE_AWARD_IDS = [
        20 => 140,
        15 => 139,
        10 => 19,
        5 => 18,
    ];

    /**
     * A header action for use in ListMembers::getHeaderActions()
     */
    public static function make(string $name = 'fixTenureAwards'): Action
    {
        return Action::make($name)
            ->label('Fix Tenure Awards')
            ->form([
                Toggle::make('persist')
                    ->label('Persist changes')
                    ->helperText('If off, runs a dry-run and only reports the counts.')
                    ->default(false),
            ])
            ->requiresConfirmation()
            ->action(function (array $data, $livewire) {
                /** @var \Filament\Resources\Pages\ListRecords $livewire */
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
        $extraneous = 0;

        foreach ($members as $member) {
            $joinDate = Carbon::parse($member->join_date);
            $years = $joinDate->diffInYears(now());

            [$eligibleAwardId, $milestone] = self::determineEligibleAward(self::TENURE_AWARD_IDS, $years);

            $missing += self::processMissingAwards($member, $eligibleAwardId, $milestone, $persist);
            $invalid += self::processInvalidAwards($member, self::TENURE_AWARD_IDS, $years, $persist);
            $extraneous += self::processExtraneousAwards($member, self::TENURE_AWARD_IDS, $milestone, $persist);
        }

        return compact('missing', 'invalid', 'extraneous');
    }

    private static function determineEligibleAward(array $awards, int $years): array
    {
        krsort($awards);
        foreach ($awards as $milestone => $awardId) {
            if ($years >= $milestone) {
                return [$awardId, $milestone];
            }
        }

        return [null, null];
    }

    private static function processMissingAwards($member, ?int $eligibleAwardId, ?int $milestone, bool $persist): int
    {
        $count = 0;
        $joinDate = Carbon::parse($member->join_date);
        $now = now();
        $years = $joinDate->diffInYears($now);

        $candidates = [];
        if ($eligibleAwardId) {
            $candidates[] = [$eligibleAwardId, $milestone];
        }

        if ($joinDate->month === $now->month) {
            [$nextAwardId, $nextMilestone] = self::determineEligibleAward(self::TENURE_AWARD_IDS, $years + 1);
            if ($nextAwardId && $nextMilestone > (int) $milestone) {
                $candidates[] = [$nextAwardId, $nextMilestone];
            }
        }

        foreach ($candidates as [$awardId, $awardYears]) {
            $has = MemberAward::where('member_id', $member->clan_id)
                ->where('award_id', $awardId)
                ->exists();

            if (! $has) {
                if ($persist) {
                    MemberAward::firstOrCreate([
                        'member_id' => $member->clan_id,
                        'award_id' => $awardId,
                    ], [
                        'reason' => "Awarded for reaching the {$awardYears}-year milestone.",
                        'approved' => true,
                    ]);
                }
                $count++;
            }
        }

        return $count;
    }

    private static function processInvalidAwards($member, array $tenureAwards, int $years, bool $persist): int
    {
        $count = 0;
        [, $milestone] = self::determineEligibleAward($tenureAwards, $years);
        $upcoming = null;

        if (Carbon::parse($member->join_date)->month === now()->month) {
            [$nextAwardId, $nextMilestone] = self::determineEligibleAward($tenureAwards, $years + 1);
            if ($nextAwardId && $nextMilestone > (int) $milestone) {
                $upcoming = $nextAwardId;
            }
        }

        $awards = MemberAward::where('member_id', $member->clan_id)
            ->whereIn('award_id', array_values($tenureAwards))
            ->get();

        foreach ($awards as $award) {
            if ($award->award_id === $upcoming) {
                continue;
            }

            $valid = false;
            foreach ($tenureAwards as $yrs => $awardId) {
                if ($award->award_id === $awardId && $years >= $yrs) {
                    $valid = true;
                    break;
                }
            }
            if (! $valid) {
                if ($persist) {
                    $award->delete();
                }
                $count++;
            }
        }

        return $count;
    }

    private static function processExtraneousAwards($member, array $tenureAwards, ?int $milestone, bool $persist): int
    {
        if (! $milestone) {
            return 0;
        }

        $joinDate = Carbon::parse($member->join_date);
        $now = now();
        $years = $joinDate->diffInYears($now);

        $effective = $milestone;
        if ($joinDate->month === $now->month) {
            [, $nextMilestone] = self::determineEligibleAward($tenureAwards, $years + 1);
            if ($nextMilestone > $effective) {
                $effective = $nextMilestone;
            }
        }

        $keep = [$tenureAwards[$milestone]];
        if ($effective !== $milestone) {
            $keep[] = $tenureAwards[$effective];
        }

        $toRemove = MemberAward::where('member_id', $member->clan_id)
            ->whereIn('award_id', array_diff($tenureAwards, $keep))
            ->get();

        $removed = 0;
        foreach ($toRemove as $award) {
            if ($persist) {
                $award->delete();
            }
            $removed++;
        }

        return $removed;
    }

    private static function notifySummary(array $summary, bool $persist): void
    {
        $mode = $persist ? 'Applied' : 'Dry-run';
        $body = implode('<br />', [
            "{$mode} tenure award corrections:",
            '- Missing awards ' . ($persist ? 'added' : 'found') . ": {$summary['missing']}",
            '- Invalid awards ' . ($persist ? 'removed' : 'found') . ": {$summary['invalid']}",
            '- Extraneous awards ' . ($persist ? 'removed' : 'found') . ": {$summary['extraneous']}",
        ]);

        Notification::make()
            ->title('Tenure Awards – Summary')
            ->body($body)
            ->success()
            ->persistent()
            ->send();
    }
}
