<?php

namespace Tests\Feature\Filament;

use App\Filament\Admin\Widgets\DivisionLeaderboardWidget;
use App\Models\Division;
use App\Models\Member;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;
use Tests\Traits\CreatesMembers;

class DivisionLeaderboardWidgetTest extends TestCase
{
    use CreatesDivisions;
    use CreatesMembers;
    use RefreshDatabase;

    #[Test]
    public function voice_leaderboard_excludes_shutting_down_divisions(): void
    {
        $active       = $this->seedDivisionWithMembers();
        $shuttingDown = $this->seedDivisionWithMembers();
        $shuttingDown->update(['shutdown_at' => now()]);

        $this->actingAs($this->createAdmin());

        Livewire::test(DivisionLeaderboardWidget::class)
            ->assertSee($active->name)
            ->assertDontSee($shuttingDown->name);
    }

    #[Test]
    public function voice_leaderboard_excludes_inactive_divisions(): void
    {
        $active   = $this->seedDivisionWithMembers();
        $inactive = $this->seedDivisionWithMembers();
        $inactive->update(['active' => false]);

        $this->actingAs($this->createAdmin());

        Livewire::test(DivisionLeaderboardWidget::class)
            ->assertSee($active->name)
            ->assertDontSee($inactive->name);
    }

    #[Test]
    public function recruiting_leaderboard_excludes_shutting_down_divisions(): void
    {
        $active       = $this->seedDivisionWithMembers();
        $shuttingDown = $this->seedDivisionWithMembers();
        $shuttingDown->update(['shutdown_at' => now()]);

        $this->actingAs($this->createAdmin());

        Livewire::test(DivisionLeaderboardWidget::class)
            ->set('leaderboardType', 'recruiting')
            ->assertSee($active->name)
            ->assertDontSee($shuttingDown->name);
    }

    private function seedDivisionWithMembers(int $memberCount = 3): Division
    {
        $division = $this->createActiveDivision();

        Member::factory()->count($memberCount)->create(['division_id' => $division->id]);

        return $division;
    }
}
