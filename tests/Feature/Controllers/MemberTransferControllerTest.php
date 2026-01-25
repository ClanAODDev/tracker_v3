<?php

namespace Tests\Feature\Controllers;

use App\Enums\Rank;
use App\Models\Division;
use App\Models\Transfer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;
use Tests\Traits\CreatesMembers;

class MemberTransferControllerTest extends TestCase
{
    use CreatesDivisions;
    use CreatesMembers;
    use RefreshDatabase;

    public function test_member_can_request_transfer_to_another_division()
    {
        Notification::fake();

        $sourceDivision = $this->createActiveDivision();
        $targetDivision = $this->createActiveDivision();

        $user = $this->createMemberWithUser([
            'division_id' => $sourceDivision->id,
            'rank' => Rank::PRIVATE_FIRST_CLASS,
        ]);

        $this->actingAs($user)
            ->postJson('/settings/transfer-request', [
                'division_id' => $targetDivision->id,
            ])
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('transfers', [
            'member_id' => $user->member->id,
            'division_id' => $targetDivision->id,
        ]);
    }

    public function test_non_officer_transfer_is_auto_approved()
    {
        Notification::fake();

        $sourceDivision = $this->createActiveDivision();
        $targetDivision = $this->createActiveDivision();

        $user = $this->createMemberWithUser([
            'division_id' => $sourceDivision->id,
            'rank' => Rank::PRIVATE_FIRST_CLASS,
        ]);

        $response = $this->actingAs($user)
            ->postJson('/settings/transfer-request', [
                'division_id' => $targetDivision->id,
            ])
            ->assertOk();

        $response->assertJson([
            'success' => true,
            'auto_approved' => true,
        ]);

        $transfer = Transfer::where('member_id', $user->member->id)->first();
        $this->assertNotNull($transfer->approved_at);
    }

    public function test_officer_transfer_requires_approval()
    {
        Notification::fake();

        $sourceDivision = $this->createActiveDivision();
        $targetDivision = $this->createActiveDivision();

        $user = $this->createMemberWithUser([
            'division_id' => $sourceDivision->id,
            'rank' => Rank::LANCE_CORPORAL,
        ]);

        $response = $this->actingAs($user)
            ->postJson('/settings/transfer-request', [
                'division_id' => $targetDivision->id,
            ])
            ->assertOk();

        $response->assertJson([
            'success' => true,
            'auto_approved' => false,
        ]);

        $transfer = Transfer::where('member_id', $user->member->id)->first();
        $this->assertNull($transfer->approved_at);
    }

    public function test_cannot_transfer_to_same_division()
    {
        $division = $this->createActiveDivision();

        $user = $this->createMemberWithUser([
            'division_id' => $division->id,
            'rank' => Rank::PRIVATE_FIRST_CLASS,
        ]);

        $this->actingAs($user)
            ->postJson('/settings/transfer-request', [
                'division_id' => $division->id,
            ])
            ->assertStatus(400)
            ->assertJson(['error' => 'You cannot transfer to your current division']);
    }

    public function test_cannot_transfer_if_pending_transfer_exists()
    {
        Notification::fake();

        $sourceDivision = $this->createActiveDivision();
        $targetDivision = $this->createActiveDivision();
        $anotherDivision = $this->createActiveDivision();

        $user = $this->createMemberWithUser([
            'division_id' => $sourceDivision->id,
            'rank' => Rank::LANCE_CORPORAL,
        ]);

        Transfer::factory()->pending()->create([
            'member_id' => $user->member->id,
            'division_id' => $targetDivision->id,
        ]);

        $this->actingAs($user)
            ->postJson('/settings/transfer-request', [
                'division_id' => $anotherDivision->id,
            ])
            ->assertStatus(400)
            ->assertJson(['error' => 'You already have a pending transfer request']);
    }

    public function test_cannot_transfer_within_one_week_of_last_transfer()
    {
        Notification::fake();

        $sourceDivision = $this->createActiveDivision();
        $targetDivision = $this->createActiveDivision();
        $anotherDivision = $this->createActiveDivision();

        $user = $this->createMemberWithUser([
            'division_id' => $sourceDivision->id,
            'rank' => Rank::PRIVATE_FIRST_CLASS,
        ]);

        Transfer::factory()->approved()->create([
            'member_id' => $user->member->id,
            'division_id' => $targetDivision->id,
            'created_at' => now()->subDays(3),
        ]);

        $response = $this->actingAs($user)
            ->postJson('/settings/transfer-request', [
                'division_id' => $anotherDivision->id,
            ])
            ->assertStatus(400);

        $this->assertStringContainsString(
            'Transfer requests can only be made once per week',
            $response->json('error')
        );
    }

    public function test_can_transfer_after_one_week()
    {
        Notification::fake();

        $sourceDivision = $this->createActiveDivision();
        $targetDivision = $this->createActiveDivision();
        $anotherDivision = $this->createActiveDivision();

        $user = $this->createMemberWithUser([
            'division_id' => $sourceDivision->id,
            'rank' => Rank::PRIVATE_FIRST_CLASS,
        ]);

        Transfer::factory()->approved()->create([
            'member_id' => $user->member->id,
            'division_id' => $targetDivision->id,
            'created_at' => now()->subDays(8),
        ]);

        $this->actingAs($user)
            ->postJson('/settings/transfer-request', [
                'division_id' => $anotherDivision->id,
            ])
            ->assertOk()
            ->assertJson(['success' => true]);
    }

    public function test_cannot_transfer_to_floater_division()
    {
        $sourceDivision = $this->createActiveDivision();
        $floaterDivision = Division::factory()->create(['name' => 'Floater', 'active' => true]);

        $user = $this->createMemberWithUser([
            'division_id' => $sourceDivision->id,
            'rank' => Rank::PRIVATE_FIRST_CLASS,
        ]);

        $this->actingAs($user)
            ->postJson('/settings/transfer-request', [
                'division_id' => $floaterDivision->id,
            ])
            ->assertStatus(400)
            ->assertJson(['error' => 'You cannot transfer to this division']);
    }

    public function test_cannot_transfer_to_bluntz_reserves()
    {
        $sourceDivision = $this->createActiveDivision();
        $reservesDivision = Division::factory()->create(['name' => "Bluntz' Reserves", 'active' => true]);

        $user = $this->createMemberWithUser([
            'division_id' => $sourceDivision->id,
            'rank' => Rank::PRIVATE_FIRST_CLASS,
        ]);

        $this->actingAs($user)
            ->postJson('/settings/transfer-request', [
                'division_id' => $reservesDivision->id,
            ])
            ->assertStatus(400)
            ->assertJson(['error' => 'You cannot transfer to this division']);
    }

    public function test_cannot_transfer_from_floater_division()
    {
        $floaterDivision = Division::factory()->create(['name' => 'Floater', 'active' => true]);
        $targetDivision = $this->createActiveDivision();

        $user = $this->createMemberWithUser([
            'division_id' => $floaterDivision->id,
            'rank' => Rank::PRIVATE_FIRST_CLASS,
        ]);

        $this->actingAs($user)
            ->postJson('/settings/transfer-request', [
                'division_id' => $targetDivision->id,
            ])
            ->assertStatus(400)
            ->assertJson(['error' => 'You cannot transfer from your current division']);
    }

    public function test_cannot_transfer_to_inactive_division()
    {
        $sourceDivision = $this->createActiveDivision();
        $inactiveDivision = Division::factory()->create(['active' => false]);

        $user = $this->createMemberWithUser([
            'division_id' => $sourceDivision->id,
            'rank' => Rank::PRIVATE_FIRST_CLASS,
        ]);

        $this->actingAs($user)
            ->postJson('/settings/transfer-request', [
                'division_id' => $inactiveDivision->id,
            ])
            ->assertStatus(400)
            ->assertJson(['error' => 'Target division is not available for transfers']);
    }

    public function test_cannot_transfer_to_shutdown_division()
    {
        $sourceDivision = $this->createActiveDivision();
        $shutdownDivision = Division::factory()->create([
            'active' => true,
            'shutdown_at' => now()->subDay(),
        ]);

        $user = $this->createMemberWithUser([
            'division_id' => $sourceDivision->id,
            'rank' => Rank::PRIVATE_FIRST_CLASS,
        ]);

        $this->actingAs($user)
            ->postJson('/settings/transfer-request', [
                'division_id' => $shutdownDivision->id,
            ])
            ->assertStatus(400)
            ->assertJson(['error' => 'Target division is not available for transfers']);
    }
}
