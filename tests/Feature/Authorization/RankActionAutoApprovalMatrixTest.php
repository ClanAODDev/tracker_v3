<?php

namespace Tests\Feature\Authorization;

use App\Enums\Position;
use App\Enums\Rank;
use App\Filament\Mod\Resources\MemberAwardResource;
use App\Filament\Mod\Resources\RankActionResource\Pages\CreateRankAction;
use App\Models\Award;
use App\Models\Member;
use App\Models\MemberAward;
use App\Models\RankAction;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Throwable;

class RankActionAutoApprovalMatrixTest extends PermissionMatrixTestCase
{
    private const TARGETS = ['same_squad', 'same_platoon_other_squad', 'other_platoon', 'unassigned', 'other_division'];

    private const RANKS = [Rank::SPECIALIST, Rank::LANCE_CORPORAL, Rank::CORPORAL, Rank::SERGEANT, Rank::STAFF_SERGEANT];

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake();
        Notification::fake();
        Queue::fake();
    }

    #[Test]
    public function auto_approval_matches_the_recorded_matrix(): void
    {
        $this->buildWorld();

        $lines = $this->autoApprovalLines('default limit (PFC)');

        $this->world->divisionA->settings()->set('max_platoon_leader_rank', Rank::CORPORAL->value);
        $this->world->divisionA->save();

        foreach ($this->world->users as $user) {
            $user->unsetRelation('division');
        }

        $lines = [...$lines, ...$this->autoApprovalLines('limit raised to Cpl')];

        $this->assertMatchesSnapshot('RankActionAutoApproval', $lines);
    }

    #[Test]
    public function chosen_promotion_rank_matches_the_recorded_matrix(): void
    {
        $this->buildWorld();
        Filament::setCurrentPanel('mod');

        $lines = [];

        foreach (PermissionWorld::ACTORS as $actor) {
            $this->actAs($actor);
            $target = Member::factory()->create([
                'name'        => "promotion target for {$actor}",
                'rank'        => Rank::PRIVATE_FIRST_CLASS,
                'position'    => Position::MEMBER,
                'division_id' => $this->world->divisionA->id,
                'platoon_id'  => $this->world->platoons['A1']->id,
                'squad_id'    => $this->world->squads['A1a']->id,
            ]);

            $lines[] = self::line('create promotion', 'PFC asking for Cpl', $actor, $this->safely(function () use ($target) {
                $page = Livewire::test(CreateRankAction::class)
                    ->fillForm(['member_id' => $target->id, 'action' => 'promotion', 'promotion_rank' => Rank::CORPORAL->value, 'Justification' => 'Characterization'])
                    ->call('create');

                $action = RankAction::where('member_id', $target->id)->first();

                return $action
                    ? 'saved as ' . $action->rank->getLabel() . ($action->approved_at ? ', approved' : ', pending')
                    : 'not saved: ' . implode(' | ', $page->errors()->keys());
            }));
        }

        $this->assertMatchesSnapshot('RankActionChosenPromotion', $lines);
    }

    #[Test]
    public function member_award_badge_matches_the_recorded_matrix(): void
    {
        $this->buildWorld();

        $divisionAward = Award::factory()->create(['division_id' => $this->world->divisionA->id]);
        $clanAward     = Award::factory()->create(['division_id' => null]);

        foreach ([$divisionAward, $divisionAward, $clanAward] as $award) {
            MemberAward::factory()->create(['award_id' => $award->id, 'member_id' => $this->world->targets['same_squad']->id, 'approved' => 0]);
        }

        $lines = [];

        foreach (PermissionWorld::ACTORS as $actor) {
            $this->actAs($actor);
            cache()->flush();

            $lines[] = self::line('member award nav badge', '2 division A + 1 clan-wide pending', $actor, $this->safely(fn () => MemberAwardResource::getNavigationBadge() ?? 'none'));
        }

        $this->assertMatchesSnapshot('MemberAwardBadge', $lines);
    }

    private function autoApprovalLines(string $setting): array
    {
        $lines = [];

        foreach (PermissionWorld::ACTORS as $actor) {
            $user = $this->world->users[$actor];

            foreach (self::TARGETS as $target) {
                foreach (self::RANKS as $rank) {
                    $this->actAs($actor);
                    $member = $this->world->targets[$target];

                    $lines[] = self::line("auto-approve ({$setting})", "{$target} to {$rank->getLabel()}", $actor, $user->member === null
                        ? 'n/a'
                        : $this->safely(fn () => User::autoApprovedTimestampForRank((string) $rank->value, $member, true) ? 'auto-approved' : 'needs approval'));
                }
            }
        }

        return $lines;
    }

    private function safely(callable $resolve): string
    {
        try {
            return (string) $resolve();
        } catch (Throwable $e) {
            return 'error:' . class_basename($e);
        }
    }
}
