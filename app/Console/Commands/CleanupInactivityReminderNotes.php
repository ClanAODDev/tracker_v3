<?php

namespace App\Console\Commands;

use App\Models\ActivityReminder;
use App\Models\Member;
use App\Models\Note;
use Illuminate\Console\Command;

class CleanupInactivityReminderNotes extends Command
{
    protected $signature = 'notes:cleanup-inactivity-reminders
                            {--dry-run : Show what would be deleted without making changes}
                            {--max-length=100 : Maximum note length to consider for cleanup}
                            {--show-samples : Show sample notes that would be deleted}
                            {--show-missed : Show notes that might be missed by current patterns}';

    protected $description = 'Soft delete short inactivity reminder notes and migrate last reminder dates to member records';

    protected array $reminderPatterns = [
        'inactivity notice',
        'inactivity reminder',
        'inactivity warning',
        'inactivity msg',
        'inactivity report',
        'inactivity massage sent',
        'inactivity message',
        'inactivity pm',
        'inactivity dm',
        'inactivity check',
        'inactivity note',
        'inactive notice',
        'inactive pm',
        'inactive msg',
        'inactive message',
        'inactive reminder',
        'activity reminder',
        'activity notice',
        'activity pm',
        'activity msg',
        'activity message',
        'acitivity notice',
        'welfare check',
        'wellness check',
        'forum inactivity',
        'forum activity',
        'forum inactive',
        'forum notice',
        'forum reminder',
        'forum dm sent',
        'forum pm sent',
        'sent forum pm',
        'sent forum dm',
        'discord inactivity',
        'discord inactive',
        'ts inactivity',
        'ts inactive',
        'final notice',
        'final reminder',
        'final inactivity',
        'final warning',
        '14 day',
        '30 day',
        '45 day',
        '60 day',
        '85 day',
        '90 day',
        '1 week',
        '2 week',
        '3 week',
        '4 week',
        '1 month',
        '2 month',
        'days inactive',
        'days over',
        'days behind',
        'no activity',
        'messaged regarding inactivity',
        'messaged regarding activity',
        'messaged about inactivity',
        'messaged about activity',
        'messaged for inactivity',
        'messaged for activity',
        'messaged via forum',
        'messaged on forum',
        'contacted regarding activity',
        'contacted regarding inactivity',
        'contacted about activity',
        'contacted about inactivity',
        'reached out for inactivity',
        'reached out regarding inactivity',
        'reached out about inactivity',
        'sent inactivity',
        'sent inactive',
        'inactivity sent',
        'inactive sent',
        'pm sent regarding',
        'dm sent regarding',
        'pm sent for inactivity',
        'pm sent about inactivity',
        'dm sent for inactivity',
        'dm sent about inactivity',
        'msg sent for inactivity',
        'msg sent about inactivity',
        'message sent for inactivity',
        'pm for inactive',
        'dm for inactive',
        'pm for inactivity',
        'dm for inactivity',
        'pm about inactivity',
        'dm about inactivity',
        'pm\'d about inactivity',
        'pm\'d for inactivity',
        'pmd about inactivity',
        'pmd for inactivity',
        'reminder about inactivity',
        'send message because of inactivity',
        'sent message because of inactivity',
        'sent msg for inactivity',
        'sent pm for inactivity',
        'sent pm about inactivity',
        'sent dm for inactivity',
        'sent dm about inactivity',
        'member inactivity',
        'sent notice',
        'notice sent for inactivity',
        'send notice',
        'sent discord pm regarding',
        'sent discord pm about',
        'sent discord notice',
        'discord message about inactivity',
        'discord pm about inactivity',
        'discord dm about inactivity',
        'reminded of inactivity',
        'pm\'d notice for inactivity',
        'pmd notice for inactivity',
        'msg\'d for inactivity',
        'msgd for inactivity',
        'message for inactivity',
        'sent message through discord',
        'message sent about inactivity',
        '21 day',
        '35 day',
        '40 day',
        '50 day',
        '70 day',
        '80 day',
        'days reminder',
        'day reminder',
        'sent reminder about',
        'reminder about forum',
        'reminder to log',
        'sent 30day',
        'sent 60day',
        'weeks inactivity',
        'sent forum and discord',
        'forum and discord pm',
        'forum pm and discord',
        'sent msg on forum',
        'forum pm on inactivity',
        'discord message sent',
        'discord pm sent',
        'discord dm sent',
        'reminder sent on discord',
        'two week reminder',
        'reached out in discord',
        'reached out about inactivity',
        'pm\'d member on discord',
        'pmd member on discord',
        'pmed for inactivity',
        'pm\'ed for inactivity',
        '3 month',
        'messaged on discord about',
        'messaged member about inactivity',
        'sent pm about 30',
        '80+ day',
        '30+ day',
        'sent a pm on forums',
        'msg for inactivity',
        'last notice sent',
        'sent forums inactivity',
        'sent discord message regarding',
        'message about inactivity',
        'reminder for inactivity',
        'inactivity discord pm',
        'notice sent at',
        'pm sent for inactivty',
        'sent forum pm about',
        'sent forum pm regarding',
        'sent forum pm on',
        'send forum pm',
        'send discord pm',
        'discord pm regarding',
        'discord dm regarding',
        'discord message regarding',
        'sent pm regarding inactivity',
        'origin pm regarding inactivity',
        'teamspeak inactivity',
        'pm\'d inactivity',
        'pmd inactivity',
        'pmed about inactivity',
        'messaged reg activity',
        'sent final pm regarding',
        'message sent on forums regarding',
        'pm for forum post',
        'reminder to post on',
        'pinged for inactivity',
        'sent forum message regarding',
        'no ts activity',
        'forum & teamspeak',
        '14-day notice',
        'wellness message',
        'sended forum pm',
        'sent forum and origin',
        'sent discord pm to remind',
        'inactive on ts',
        'inactivity - no reply',
        'inactivity, no reply',
        'reached out about inactivity',
        'reached out to user about',
        'forum inactiviy',
        'forum pm activity',
        'activity sent',
        'froum dm',
        'messaged about posting',
        'reached out about monthly',
        'reached out about forum',
        'messaged on discord',
        'notice sent 1',
        'notice sent 2',
        'notice sent 3',
        'notice sent 4',
        'notice sent 5',
        'notice sent 6',
        'notice sent 7',
        'notice sent 8',
        'notice sent 9',
        '28 day',
        'reached out in regard to',
        'notice at 36',
        'notice at 30',
        'notice at 40',
        'sent reminder on discord',
        'inactivty',
        'inactvity',
        'inactviity',
        'forum inactvity',
        'sent note about inactivity',
        'sent message of inactivity',
        'sent pm through discord about',
        'forum pm on activity',
        'inactive, no response',
        'inactivity limit',
        'sent pm via discord regarding',
        'messaged re:inactivity',
        'messaged re: inactivity',
        'member messaged',
        '30+++ day',
        'pm sent on forum and',
        'messaged on 6',
        'messaged on 7',
        'messaged on 8',
        'messaged on 9',
        'messaged on 10',
        'messaged on 11',
        'messaged on 12',
        'forum pm for inactivity',
        'send note in forums',
        '30 notice sent',
        '30day notice',
        '60day notice',
        'discord reminder sent',
        'sent note regarding inactivity',
        'messaged about their inactivity',
        'member was messaged about',
        'forum pm',
        'messaged',
        'sent discord pm',
        'sent pm',
        'pm sent',
        'dm sent',
        'sent dm',
        'notice sent',
        'reminder sent',
        'reminder pm',
        'inactive for',
        'inactive in the game',
        'inactive on forum',
        'inactive on ts',
        'inactive warning',
        'month inactive',
        'pm\'d member for',
        'pmd member for',
        'reminder to be active',
        'sent him a',
        'sent her a',
        '10 day warning',
        '(30) day',
        '(30+) day',
        '(60) day',
        'sent reminder to post',
        'sent reminder for member',
        'sent a second pm',
        'inactivity for',
        '14 inactivity',
    ];

    protected array $standalonePatterns = [
        'inactivity',
        'inactive',
    ];

    protected array $excludePatterns = [
        'removed for inactivity',
        'removed for inactive',
        'removed due to inactivity',
        'removal',
        'promoted',
        'promotion',
        'welcome',
        'introduction email',
        'ts violation',
        'ts reminder',
        'teamspeak violation',
        'teamspeak reminder',
        'loa expired',
        'loa expiration',
        'leave expired',
        'leave of absence',
        'leave request',
        'ending loa',
        'mass pm',
        'recruiting',
        'flagged for inactivity',
        'flagged member',
        'flagged notice',
        'flag notice',
        'tracker',
        'discord for the tracker',
        'coc violation',
        'code of conduct',
        'forum post notice',
        'left clan',
        'move to discord',
        'inactived out',
        'no notice',
        'kicked for inactivity',
        'in-game reminder',
        'in game reminder',
        'reached out via pm to resign',
        'contacted wrt loa',
        'resign',
        'removed from in-game',
        'removed from in game',
        'rule reminder',
        'not in ts when',
        'not in ts while',
        'no response to transfer',
        'wasn\'t a good fit',
        'not a good fit',
        'didn\'t want to be',
        'did not want to be',
        'decided aod',
        'changed his tags',
        'changed her tags',
        'removed tags',
        'without being on ts',
        'messaged with no response ingame',
        'messaged via game chat',
        'another reminder sent for not being',
        'remove user from aod',
        'remove for inactivity',
        'reached out in game',
        'left in game squadron',
        'without notice',
        'reminding user to be in teamspeak',
        'requested to leave',
        'has left our servers',
        'concerning vipress',
        'not being in ts whilst',
        'friend request',
        'loa ends',
        'reached out about loa',
        'reached out for update on status',
        'messaged in game for not being',
        'reminder sent to be in ts while',
        'removed for in game inactivity',
        'reminder sent through ingame',
        'sent discord msg for welfare',
        'contacted by his wife',
        'contacted by her wife',
        'rejoining aod',
        'called the forums',
        'expired loa',
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

        if ($this->option('show-missed')) {
            $this->showMissedNotes($query, $maxLength);
        }

        $membersAffected = $query->distinct('member_id')->count('member_id');
        $this->info("These notes belong to {$membersAffected} members");

        if (! $dryRun && ! $this->confirm('Proceed with cleanup?')) {
            $this->info('Cancelled.');

            return self::SUCCESS;
        }

        $this->info('Processing notes and updating member records...');

        $memberIds = $this->buildQuery($maxLength)
            ->distinct()
            ->pluck('member_id')
            ->all();

        $bar = $this->output->createProgressBar(count($memberIds));
        $bar->start();

        $notesDeleted = 0;
        $membersUpdated = 0;
        $remindersCreated = 0;

        $notesForceDeleted = 0;

        foreach (array_chunk($memberIds, 100) as $memberIdBatch) {
            foreach ($memberIdBatch as $memberId) {
                $member = Member::withTrashed()->find($memberId);

                if (! $member) {
                    $bar->advance();

                    continue;
                }

                $lastRemovalDate = $this->getLastRemovalDate($memberId);
                $memberIsRemoved = $member->division_id === 0 || $member->trashed();

                $reminderNotes = $this->buildQuery($maxLength)
                    ->where('member_id', $memberId)
                    ->with('author')
                    ->orderBy('created_at', 'desc')
                    ->get();

                if (! $dryRun) {
                    foreach ($reminderNotes as $note) {
                        $shouldForceDelete = $memberIsRemoved
                            || ($lastRemovalDate && $note->created_at < $lastRemovalDate);

                        if ($shouldForceDelete) {
                            $note->forceDelete();
                            $notesForceDeleted++;

                            continue;
                        }

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

                        $note->delete();
                        $notesDeleted++;
                    }

                    if (! $memberIsRemoved) {
                        $latestReminder = ActivityReminder::where('member_id', $member->clan_id)
                            ->orderByDesc('created_at')
                            ->first();

                        if ($latestReminder) {
                            $member->update([
                                'last_activity_reminder_at' => $latestReminder->created_at,
                                'activity_reminded_by_id' => $latestReminder->reminded_by_id,
                            ]);
                        }
                    }
                } else {
                    foreach ($reminderNotes as $note) {
                        $shouldForceDelete = $memberIsRemoved
                            || ($lastRemovalDate && $note->created_at < $lastRemovalDate);

                        if ($shouldForceDelete) {
                            $notesForceDeleted++;
                        } else {
                            $notesDeleted++;
                            $remindersCreated++;
                        }
                    }
                }

                $membersUpdated++;
                $bar->advance();
            }
        }

        $bar->finish();
        $this->newLine(2);

        $removalNotesReclassified = $this->reclassifyRemovalNotes($dryRun);

        $this->info('Cleanup complete:');
        $this->table(
            ['Metric', 'Count'],
            [
                ['Notes soft-deleted (converted to reminders)', $notesDeleted],
                ['Notes permanently deleted (pre-removal)', $notesForceDeleted],
                ['Members updated', $membersUpdated],
                ['Removal notes reclassified', $removalNotesReclassified],
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

                foreach ($this->standalonePatterns as $pattern) {
                    $q->orWhere(function ($sq) use ($pattern) {
                        $sq->whereRaw('LOWER(TRIM(body)) = ?', [$pattern])
                            ->orWhereRaw('LOWER(TRIM(body)) = ?', [$pattern . '.']);
                    });
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

    protected function showMissedNotes($caughtQuery, int $maxLength): void
    {
        $this->newLine();
        $this->info('Checking for potentially missed inactivity notes...');

        $potentialKeywords = [
            'inactiv',
            'inactive',
            'forum pm',
            'forum dm',
            'discord pm',
            'discord dm',
            'notice',
            'reminder',
            'wellness',
            'welfare',
            'reached out',
            'messaged',
            'contacted',
        ];

        $missed = Note::query()
            ->whereRaw('LENGTH(body) <= ?', [$maxLength])
            ->whereNotIn('id', $caughtQuery->clone()->select('id'))
            ->where(function ($q) use ($potentialKeywords) {
                foreach ($potentialKeywords as $keyword) {
                    $q->orWhereRaw('LOWER(body) LIKE ?', ['%' . $keyword . '%']);
                }
            })
            ->where(function ($q) {
                foreach ($this->excludePatterns as $pattern) {
                    $q->whereRaw('LOWER(body) NOT LIKE ?', ['%' . $pattern . '%']);
                }
            })
            ->selectRaw('body, COUNT(*) as cnt')
            ->groupBy('body')
            ->orderByDesc('cnt')
            ->limit(50)
            ->get();

        if ($missed->isEmpty()) {
            $this->info('No potentially missed notes found!');
        } else {
            $this->warn('Potentially missed notes (review and add patterns if needed):');
            $this->newLine();

            foreach ($missed as $note) {
                $this->line("  {$note->cnt}x: {$note->body}");
            }
        }

        $this->newLine();
    }

    protected function reclassifyRemovalNotes(bool $dryRun): int
    {
        $query = Note::where('type', 'negative')
            ->where(function ($q) {
                $q->whereRaw("LOWER(body) LIKE 'member removal:%'")
                    ->orWhereRaw("LOWER(body) LIKE 'member removed for inactivity%'")
                    ->orWhereRaw("LOWER(body) LIKE 'removed for inactivity%'")
                    ->orWhereRaw("LOWER(body) LIKE 'removed due to inactivity%'");
            });

        $count = $query->count();

        if ($count === 0) {
            return 0;
        }

        $this->info("Found {$count} removal notes marked as negative to reclassify as misc");

        if (! $dryRun) {
            $query->update(['type' => 'misc']);
        }

        return $count;
    }

    protected function getLastRemovalDate(int $memberId): ?\Carbon\Carbon
    {
        $removalNote = Note::withTrashed()
            ->where('member_id', $memberId)
            ->where(function ($q) {
                $q->whereRaw("LOWER(body) LIKE '%removed for inactivity%'")
                    ->orWhereRaw("LOWER(body) LIKE '%removed due to inactivity%'")
                    ->orWhereRaw("LOWER(body) LIKE 'member removal:%'");
            })
            ->orderByDesc('created_at')
            ->first();

        return $removalNote?->created_at;
    }
}
