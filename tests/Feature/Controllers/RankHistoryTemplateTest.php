<?php

namespace Tests\Feature\Controllers;

use App\Enums\Rank;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;
use Tests\Traits\CreatesMembers;

class RankHistoryTemplateTest extends TestCase
{
    use CreatesDivisions;
    use CreatesMembers;
    use RefreshDatabase;

    #[Test]
    public function guest_cannot_download_template(): void
    {
        $this->get(route('rank-history.template'))
            ->assertRedirect();
    }

    #[Test]
    public function authenticated_user_can_download_template(): void
    {
        $division = $this->createActiveDivision();
        $user     = $this->createMemberWithUser(['division_id' => $division->id]);

        $this->actingAs($user)
            ->get(route('rank-history.template'))
            ->assertOk()
            ->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }

    #[Test]
    public function template_header_includes_date_format_hint(): void
    {
        $division = $this->createActiveDivision();
        $user     = $this->createMemberWithUser(['division_id' => $division->id]);

        $content = $this->actingAs($user)
            ->get(route('rank-history.template'))
            ->getContent();

        $this->assertStringStartsWith('rank,date (YYYY-MM-DD)', $content);
    }

    #[Test]
    public function template_contains_all_ranks_up_to_master_sergeant(): void
    {
        $division = $this->createActiveDivision();
        $user     = $this->createMemberWithUser(['division_id' => $division->id]);

        $content = $this->actingAs($user)
            ->get(route('rank-history.template'))
            ->getContent();

        $expected = collect(Rank::cases())
            ->filter(fn (Rank $r) => $r->value <= Rank::MASTER_SERGEANT->value)
            ->sortBy(fn (Rank $r) => $r->value);

        foreach ($expected as $rank) {
            $this->assertStringContainsString($rank->getLabel(), $content);
        }
    }

    #[Test]
    public function template_does_not_contain_ranks_above_master_sergeant(): void
    {
        $division = $this->createActiveDivision();
        $user     = $this->createMemberWithUser(['division_id' => $division->id]);

        $content = $this->actingAs($user)
            ->get(route('rank-history.template'))
            ->getContent();

        $excluded = collect(Rank::cases())
            ->filter(fn (Rank $r) => $r->value > Rank::MASTER_SERGEANT->value);

        foreach ($excluded as $rank) {
            $this->assertStringNotContainsString($rank->getLabel(), $content);
        }
    }
}
