<?php

namespace Tests\Unit\Notifications;

use App\Models\Award;
use App\Models\MemberAward;
use App\Notifications\Channel\NotifyDivisionMemberAwardsBulk;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;
use Tests\Traits\CreatesMembers;

class NotifyDivisionMemberAwardsBulkTest extends TestCase
{
    use CreatesDivisions;
    use CreatesMembers;
    use RefreshDatabase;

    private function divisionWithAwardAlertsEnabled()
    {
        $division = $this->createActiveDivision();

        $division->settings = array_merge($division->settings, [
            'chat_alerts' => array_merge($division->settings['chat_alerts'], ['member_awarded' => 'officers']),
        ]);
        $division->save();

        return $division;
    }

    #[Test]
    public function groups_recipients_by_award_type_into_separate_fields()
    {
        $division = $this->divisionWithAwardAlertsEnabled();

        $fiveYear = Award::factory()->global()->create(['name' => '5 Year Tenure']);
        $tenYear  = Award::factory()->global()->create(['name' => '10 Year Tenure']);

        $alice = $this->createMember(['division_id' => $division->id, 'name' => 'Alice']);
        $bob   = $this->createMember(['division_id' => $division->id, 'name' => 'Bob']);
        $carol = $this->createMember(['division_id' => $division->id, 'name' => 'Carol']);

        $awards = Collection::make([
            MemberAward::factory()->approved()->create(['member_id' => $alice->id, 'award_id' => $fiveYear->id]),
            MemberAward::factory()->approved()->create(['member_id' => $bob->id, 'award_id' => $fiveYear->id]),
            MemberAward::factory()->approved()->create(['member_id' => $carol->id, 'award_id' => $tenYear->id]),
        ])->load('member', 'award');

        $message = (new NotifyDivisionMemberAwardsBulk($awards))->toBot($division);
        $fields  = $message['body']['embeds'][0]['fields'];

        $this->assertCount(2, $fields);

        $fiveYearField = collect($fields)->firstWhere('name', '5 Year Tenure');
        $tenYearField  = collect($fields)->firstWhere('name', '10 Year Tenure');

        $this->assertStringContainsString('Alice', $fiveYearField['value']);
        $this->assertStringContainsString('Bob', $fiveYearField['value']);
        $this->assertStringNotContainsString('Carol', $fiveYearField['value']);

        $this->assertStringContainsString('Carol', $tenYearField['value']);
        $this->assertStringNotContainsString('Alice', $tenYearField['value']);
    }

    #[Test]
    public function routes_to_the_divisions_configured_channel()
    {
        $division = $this->divisionWithAwardAlertsEnabled();
        $award    = Award::factory()->global()->create();
        $member   = $this->createMember(['division_id' => $division->id]);

        $awards = Collection::make([
            MemberAward::factory()->approved()->create(['member_id' => $member->id, 'award_id' => $award->id]),
        ])->load('member', 'award');

        $message = (new NotifyDivisionMemberAwardsBulk($awards))->toBot($division);

        $this->assertStringContainsString('channels/', $message['api_uri']);
    }
}
