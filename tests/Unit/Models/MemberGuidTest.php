<?php

namespace Tests\Unit\Models;

use App\Models\Member;
use App\Support\MemberCard;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\CreatesMembers;

class MemberGuidTest extends TestCase
{
    use CreatesMembers;
    use RefreshDatabase;

    #[Test]
    public function creating_a_member_generates_a_ten_character_guid()
    {
        $member = $this->createMember();

        $this->assertMatchesRegularExpression('/^[A-Za-z0-9]{10}$/', $member->guid);
    }

    #[Test]
    public function member_guids_are_unique()
    {
        $guids = Member::factory()->count(25)->create()->pluck('guid');

        $this->assertSame(25, $guids->map(fn (string $guid) => strtolower($guid))->unique()->count());
    }

    #[Test]
    public function member_guid_is_never_all_digits()
    {
        Str::createRandomStringsUsingSequence(['1234567890', 'abc1234567']);

        $this->assertSame('abc1234567', Member::generateGuid());

        Str::createRandomStringsNormally();
    }

    #[Test]
    public function member_guid_cannot_be_changed_after_creation()
    {
        $member   = $this->createMember();
        $original = $member->guid;

        $member->update(['guid' => 'changed123']);

        $this->assertSame($original, $member->fresh()->guid);
    }

    #[Test]
    public function an_explicit_guid_is_kept_on_create()
    {
        $member = Member::factory()->create(['guid' => 'fixedGuid1']);

        $this->assertSame('fixedGuid1', $member->fresh()->guid);
    }

    #[Test]
    public function member_card_includes_the_guid()
    {
        $member = $this->createMember();

        $this->assertSame($member->guid, MemberCard::from($member)['guid']);
    }
}
