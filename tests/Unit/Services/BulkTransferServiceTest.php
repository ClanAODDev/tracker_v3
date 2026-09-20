<?php

namespace Tests\Unit\Services;

use App\Channels\BotChannel;
use App\Enums\Position;
use App\Jobs\UpdateDivisionForMember;
use App\Models\Division;
use App\Models\Transfer;
use App\Notifications\Channel\NotifyDivisionBulkMemberTransfer;
use App\Services\BulkTransferService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Notification;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;
use Tests\Traits\CreatesMembers;

class BulkTransferServiceTest extends TestCase
{
    use CreatesDivisions;
    use CreatesMembers;
    use RefreshDatabase;

    private BulkTransferService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(BulkTransferService::class);
    }

    private function withTransferAlertsEnabled(Division $division): Division
    {
        $division->settings = array_merge($division->settings, [
            'chat_alerts' => array_merge($division->settings['chat_alerts'], ['member_transferred' => 'officers']),
        ]);
        $division->save();

        return $division;
    }

    #[Test]
    public function it_moves_members_to_the_target_division()
    {
        Bus::fake();
        Notification::fake();

        $source = $this->createActiveDivision();
        $target = $this->createActiveDivision();

        $memberOne = $this->createMember(['division_id' => $source->id]);
        $memberTwo = $this->createMember(['division_id' => $source->id]);

        $moved = $this->service->transfer(collect([$memberOne, $memberTwo]), $target);

        $this->assertSame(2, $moved);
        $this->assertSame($target->id, $memberOne->fresh()->division_id);
        $this->assertSame($target->id, $memberTwo->fresh()->division_id);
    }

    #[Test]
    public function it_creates_an_approved_transfer_record_per_member()
    {
        Bus::fake();
        Notification::fake();

        $source = $this->createActiveDivision();
        $target = $this->createActiveDivision();
        $member = $this->createMember(['division_id' => $source->id]);

        $this->service->transfer(collect([$member]), $target);

        $transfer = Transfer::where('member_id', $member->id)->first();

        $this->assertNotNull($transfer);
        $this->assertSame($target->id, $transfer->division_id);
        $this->assertNotNull($transfer->approved_at);
    }

    #[Test]
    public function it_resets_platoon_and_squad_on_transfer()
    {
        Bus::fake();
        Notification::fake();

        $source = $this->createActiveDivision();
        $target = $this->createActiveDivision();
        $member = $this->createMember([
            'division_id' => $source->id,
            'platoon_id'  => 5,
            'squad_id'    => 9,
            'position'    => Position::MEMBER,
        ]);

        $this->service->transfer(collect([$member]), $target);

        $member->refresh();
        $this->assertSame(0, $member->platoon_id);
        $this->assertSame(0, $member->squad_id);
    }

    #[Test]
    public function it_skips_members_already_in_the_target_division()
    {
        Bus::fake();
        Notification::fake();

        $target = $this->createActiveDivision();
        $member = $this->createMember(['division_id' => $target->id]);

        $moved = $this->service->transfer(collect([$member]), $target);

        $this->assertSame(0, $moved);
        $this->assertDatabaseCount('transfers', 0);
    }

    #[Test]
    public function it_dispatches_a_forum_sync_job_per_member()
    {
        Bus::fake();
        Notification::fake();

        $source    = $this->createActiveDivision();
        $target    = $this->createActiveDivision();
        $memberOne = $this->createMember(['division_id' => $source->id]);
        $memberTwo = $this->createMember(['division_id' => $source->id]);

        $this->service->transfer(collect([$memberOne, $memberTwo]), $target);

        Bus::assertDispatchedTimes(UpdateDivisionForMember::class, 2);
    }

    #[Test]
    public function it_notifies_source_and_target_divisions_once_per_group()
    {
        Bus::fake();
        Notification::fake();

        $source    = $this->withTransferAlertsEnabled($this->createActiveDivision());
        $target    = $this->withTransferAlertsEnabled($this->createActiveDivision());
        $memberOne = $this->createMember(['division_id' => $source->id]);
        $memberTwo = $this->createMember(['division_id' => $source->id]);

        $this->service->transfer(collect([$memberOne, $memberTwo]), $target);

        Notification::assertSentTo($source, NotifyDivisionBulkMemberTransfer::class, function ($notification) {
            return $notification->via() === [BotChannel::class];
        });
        Notification::assertSentTimes(NotifyDivisionBulkMemberTransfer::class, 2);
    }

    #[Test]
    public function it_notifies_the_target_division_once_per_source_division_group()
    {
        Bus::fake();
        Notification::fake();

        $sourceOne = $this->withTransferAlertsEnabled($this->createActiveDivision());
        $sourceTwo = $this->withTransferAlertsEnabled($this->createActiveDivision());
        $target    = $this->withTransferAlertsEnabled($this->createActiveDivision());

        $memberOne = $this->createMember(['division_id' => $sourceOne->id]);
        $memberTwo = $this->createMember(['division_id' => $sourceTwo->id]);

        $this->service->transfer(collect([$memberOne, $memberTwo]), $target);

        Notification::assertSentTimes(NotifyDivisionBulkMemberTransfer::class, 4);
    }

    #[Test]
    public function it_handles_members_with_no_current_division()
    {
        Bus::fake();
        Notification::fake();

        $target = $this->createActiveDivision();
        $member = $this->createMember(['division_id' => 0]);

        $moved = $this->service->transfer(collect([$member]), $target);

        $this->assertSame(1, $moved);
        $this->assertSame($target->id, $member->fresh()->division_id);
    }
}
