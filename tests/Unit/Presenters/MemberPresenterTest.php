<?php

namespace Tests\Unit\Presenters;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;
use Tests\Traits\CreatesMembers;

class MemberPresenterTest extends TestCase
{
    use CreatesDivisions;
    use CreatesMembers;
    use RefreshDatabase;

    #[Test]
    public function activity_class_is_danger_when_never_active()
    {
        $division = $this->createActiveDivision();
        $member   = $this->createMember(['division_id' => $division->id, 'last_voice_activity' => null]);

        $this->assertSame('text-danger', $member->present()->activityClass($division));
    }

    #[Test]
    public function activity_class_is_success_within_default_threshold()
    {
        $division = $this->createActiveDivision();
        $member   = $this->createMember([
            'division_id'         => $division->id,
            'last_voice_activity' => now()->subDays(5),
        ]);

        $this->assertSame('text-success', $member->present()->activityClass($division));
    }

    #[Test]
    public function activity_class_is_warning_at_fourteen_days()
    {
        $division = $this->createActiveDivision();
        $member   = $this->createMember([
            'division_id'         => $division->id,
            'last_voice_activity' => now()->subDays(14),
        ]);

        $this->assertSame('text-warning', $member->present()->activityClass($division));
    }

    #[Test]
    public function activity_class_is_danger_at_thirty_days()
    {
        $division = $this->createActiveDivision();
        $member   = $this->createMember([
            'division_id'         => $division->id,
            'last_voice_activity' => now()->subDays(30),
        ]);

        $this->assertSame('text-danger', $member->present()->activityClass($division));
    }

    #[Test]
    public function profile_activity_class_is_empty_when_active()
    {
        $member = $this->createMember(['last_voice_activity' => now()]);

        $this->assertSame('', $member->present()->profileActivityClass());
    }

    #[Test]
    public function profile_activity_class_is_muted_when_never_active()
    {
        $member = $this->createMember(['last_voice_activity' => null]);

        $this->assertSame('text-muted', $member->present()->profileActivityClass());
    }

    #[Test]
    public function colored_name_escapes_html_in_member_name()
    {
        $member = $this->createMember(['name' => '<script>alert(1)</script>']);

        $this->assertStringNotContainsString('<script>', $member->present()->coloredName());
        $this->assertStringContainsString('&lt;script&gt;', $member->present()->coloredName());
    }
}
