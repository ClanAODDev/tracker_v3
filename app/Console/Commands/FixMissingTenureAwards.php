<?php

namespace App\Console\Commands;

use App\Models\Member;
use App\Models\MemberAward;
use Carbon\Carbon;
use Illuminate\Console\Command;

class FixMissingTenureAwards extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fix-tenure-awards {--persist : Persist changes to the database}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Assign tenure awards to members based on their join date.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $tenureAwards = [
            20 => 140, // 20 years
            15 => 139, // 15 years
            10 => 19,  // 10 years
            5 => 18,   // 5 years
        ];

        $members = Member::whereHas('division')->get();

        $persistChanges = $this->option('persist');

        $missingAwards = 0;

        foreach ($members as $member) {
            $joinDate = Carbon::parse($member->join_date);
            $yearsOfService = $joinDate->diffInYears(Carbon::now());

            $eligibleAwardId = null;
            $milestone = null;
            foreach ($tenureAwards as $years => $awardId) {
                if ($yearsOfService >= $years) {
                    $eligibleAwardId = $awardId;
                    $milestone = $years;
                    break;
                }
            }

            if ($eligibleAwardId) {
                $hasAward = MemberAward::where('member_id', $member->clan_id)
                    ->where('award_id', $eligibleAwardId)
                    ->exists();

                if (! $hasAward) {
                    $message = "Member ID {$member->clan_id} is missing award ID $eligibleAwardId for reaching $milestone years of service.";

                    ++$missingAwards;

                    if ($persistChanges) {
                        MemberAward::create([
                            'member_id' => $member->clan_id,
                            'award_id' => $eligibleAwardId,
                            'reason' => "Awarded for reaching the $milestone-year milestone.",
                            'created_at' => Carbon::now(),
                            'updated_at' => Carbon::now(),
                        ]);
                        $message .= " Added award.\n";
                    } else {
                        $message .= " [Simulation: No changes made]\n";
                    }

                    $this->info($message);
                }
            }
        }

        $this->info("Tenure awards assignment complete. Found {$missingAwards} missing awards!");
    }
}
