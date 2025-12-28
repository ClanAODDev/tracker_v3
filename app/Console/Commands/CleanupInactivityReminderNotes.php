<?php

namespace App\Console\Commands;

use App\Models\ActivityReminder;
use App\Models\Member;
use App\Models\Note;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CleanupInactivityReminderNotes extends Command
{
    protected $signature = 'notes:cleanup-inactivity-reminders
                            {--dry-run : Show what would be deleted without making changes}
                            {--max-length=100 : Maximum note length to consider for cleanup}
                            {--show-samples : Show sample notes that would be deleted}';

    protected $description = 'Clean up short inactivity reminder notes and migrate last reminder dates to member records';

    protected array $reminderPatterns = [
        'pm sent',
        'pm\'d',
        'pmd',
        'sent pm',
        'sent a pm',
        'inactivity notice',
        'inactivity reminder',
        'inactivity warning',
        'inactivity msg',
        'inactivity message',
        'inactivity pm',
        'reminder sent',
        'notice sent',
        'wellness check',
        'activity reminder',
        'activity notice',
        'activity pm',
        'activity msg',
        'activity message sent',
        'forum notice sent',
        'forum reminder',
        'forum activity pm',
        'forum inactivity',
        'discord message sent',
        'discord dm sent',
        'discord pm sent',
        'final notice sent',
        'final reminder sent',
        '14 day',
        '30 day',
        '45 day',
        '60 day',
        '2 week',
        '3 week',
        'messaged regarding inactivity',
        'messaged regarding activity',
        'messaged about inactivity',
        'messaged about activity',
        'contacted regarding activity',
        'contacted regarding inactivity',
        'contacted about activity',
        'contacted about inactivity',
    ];

    protected array $excludePatterns = [
        'removed for inactivity',
        'removal',
    ];

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        $maxLength = (int) $this->option('max-length');
        $showSamples = $this->option('show-samples');

        if ($dryRun) {
            $this->warn('DRY RUN MODE - No changes will be made');
        }

        $this->info('Building query for inactivity reminder notes...');

        $query = $this->buildQuery($maxLength);

        $totalCount = $query->count();
        $this->info("Found {$totalCount} notes matching cleanup criteria");

        if ($totalCount === 0) {
            $this->info('No notes to clean up.');

            return self::SUCCESS;
        }

        if ($showSamples) {
            $this->showSampleNotes($query);
        }

        $membersAffected = $query->distinct('member_id')->count('member_id');
        $this->info("These notes belong to {$membersAffected} members");

        if (! $dryRun && ! $this->confirm('Proceed with cleanup?')) {
            $this->info('Cancelled.');

            return self::SUCCESS;
        }

        $this->info('Processing notes and updating member records...');

        $bar = $this->output->createProgressBar($membersAffected);
        $bar->start();

        $notesDeleted = 0;
        $membersUpdated = 0;
        $remindersCreated = 0;

        $this->buildQuery($maxLength)
            ->select('member_id', DB::raw('MAX(created_at) as last_reminder_at'), DB::raw('COUNT(*) as note_count'))
            ->groupBy('member_id')
            ->orderBy('member_id')
            ->chunk(100, function ($memberGroups) use ($dryRun, $maxLength, &$notesDeleted, &$membersUpdated, &$remindersCreated, $bar) {
                foreach ($memberGroups as $group) {
                    $member = Member::where('id', $group->member_id)->first();

                    if (! $member) {
                        $bar->advance();

                        continue;
                    }

                    $reminderNotes = $this->buildQuery($maxLength)
                        ->where('member_id', $group->member_id)
                        ->with('author')
                        ->orderBy('created_at', 'desc')
                        ->get();

                    if (! $dryRun) {
                        foreach ($reminderNotes as $note) {
                            $exists = ActivityReminder::where('member_id', $member->clan_id)
                                ->whereDate('created_at', $note->created_at->toDateString())
                                ->exists();

                            if (! $exists) {
                                ActivityReminder::create([
                                    'member_id' => $member->clan_id,
                                    'division_id' => $member->division_id ?: 1,
                                    'reminded_by_id' => $note->author?->id ?? 1,
                                    'created_at' => $note->created_at,
                                    'updated_at' => $note->created_at,
                                ]);
                                $remindersCreated++;
                            }
                        }

                        $latestReminder = ActivityReminder::where('member_id', $member->clan_id)
                            ->orderByDesc('created_at')
                            ->first();

                        if ($latestReminder) {
                            $member->update([
                                'last_activity_reminder_at' => $latestReminder->created_at,
                                'activity_reminded_by_id' => $latestReminder->reminded_by_id,
                            ]);
                        }

                        $deleted = $this->buildQuery($maxLength)
                            ->where('member_id', $group->member_id)
                            ->delete();

                        $notesDeleted += $deleted;
                    } else {
                        $notesDeleted += $group->note_count;
                        $remindersCreated += $reminderNotes->count();
                    }

                    $membersUpdated++;
                    $bar->advance();
                }
            });

        $bar->finish();
        $this->newLine(2);

        $this->info('Cleanup complete:');
        $this->table(
            ['Metric', 'Count'],
            [
                ['Notes soft-deleted', $notesDeleted],
                ['Members updated', $membersUpdated],
            ]
        );

        if ($dryRun) {
            $this->warn('This was a dry run. Run without --dry-run to apply changes.');
        }

        return self::SUCCESS;
    }

    protected function buildQuery(int $maxLength)
    {
        $query = Note::query()
            ->whereRaw('LENGTH(body) <= ?', [$maxLength])
            ->where(function ($q) {
                foreach ($this->reminderPatterns as $pattern) {
                    $q->orWhereRaw('LOWER(body) LIKE ?', ['%' . $pattern . '%']);
                }
            })
            ->where(function ($q) {
                foreach ($this->excludePatterns as $pattern) {
                    $q->whereRaw('LOWER(body) NOT LIKE ?', ['%' . $pattern . '%']);
                }
            });

        return $query;
    }

    protected function showSampleNotes($query): void
    {
        $this->newLine();
        $this->info('Sample notes that would be deleted:');
        $this->newLine();

        $samples = $query->clone()
            ->inRandomOrder()
            ->limit(30)
            ->get(['body', 'created_at']);

        foreach ($samples as $note) {
            $this->line("  [{$note->created_at}] {$note->body}");
        }

        $this->newLine();
    }
}
