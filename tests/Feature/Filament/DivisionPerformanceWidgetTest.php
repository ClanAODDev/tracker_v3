<?php

namespace Tests\Feature\Filament;

use App\Filament\Admin\Widgets\DivisionPerformanceWidget;
use App\Models\Census;
use App\Models\Division;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;
use Tests\Traits\CreatesMembers;

class DivisionPerformanceWidgetTest extends TestCase
{
    use CreatesDivisions;
    use CreatesMembers;
    use RefreshDatabase;

    /**
     * assertSeeInOrder() is unreliable against this widget (its snapshot renders with
     * isTableLoaded: false), so compare raw HTML positions directly instead.
     */
    private function assertAppearsBefore(string $html, Division $first, Division $second): void
    {
        $firstPos  = strpos($html, $first->name);
        $secondPos = strpos($html, $second->name);

        $this->assertNotFalse($firstPos, "{$first->name} not found in rendered table");
        $this->assertNotFalse($secondPos, "{$second->name} not found in rendered table");
        $this->assertLessThan($secondPos, $firstPos, "{$first->name} should render before {$second->name}");
    }

    #[Test]
    public function voice_rate_column_can_be_sorted(): void
    {
        $high = $this->createActiveDivision();
        $low  = $this->createActiveDivision();
        $this->createMember(['division_id' => $high->id]);
        $this->createMember(['division_id' => $low->id]);

        Census::create([
            'division_id'         => $high->id,
            'count'               => 100,
            'weekly_active_count' => 0,
            'weekly_voice_count'  => 90,
        ]);
        Census::create([
            'division_id'         => $low->id,
            'count'               => 100,
            'weekly_active_count' => 0,
            'weekly_voice_count'  => 5,
        ]);

        $this->actingAs($this->createAdmin());

        $desc = Livewire::test(DivisionPerformanceWidget::class)->sortTable('voice_rate', 'desc');
        $this->assertAppearsBefore($desc->html(), $high, $low);

        $asc = Livewire::test(DivisionPerformanceWidget::class)->sortTable('voice_rate', 'asc');
        $this->assertAppearsBefore($asc->html(), $low, $high);
    }

    #[Test]
    public function recruits_column_can_be_sorted(): void
    {
        // $fewerRecruits has more total members but fewer recent joins, deliberately
        // decoupled from member count so this can't pass via the widget's default
        // members_count ordering leaking through instead of the requested sort.
        $moreRecruits  = $this->createActiveDivision();
        $fewerRecruits = $this->createActiveDivision();

        $this->createMember(['division_id' => $moreRecruits->id, 'join_date' => now()]);
        $this->createMember(['division_id' => $moreRecruits->id, 'join_date' => now()]);

        $this->createMember(['division_id' => $fewerRecruits->id, 'join_date' => now()]);
        $this->createMember(['division_id' => $fewerRecruits->id, 'join_date' => now()->subYears(2)]);
        $this->createMember(['division_id' => $fewerRecruits->id, 'join_date' => now()->subYears(2)]);
        $this->createMember(['division_id' => $fewerRecruits->id, 'join_date' => now()->subYears(2)]);

        $this->actingAs($this->createAdmin());

        $desc = Livewire::test(DivisionPerformanceWidget::class)->sortTable('recruits_this_month', 'desc');
        $this->assertAppearsBefore($desc->html(), $moreRecruits, $fewerRecruits);

        $asc = Livewire::test(DivisionPerformanceWidget::class)->sortTable('recruits_this_month', 'asc');
        $this->assertAppearsBefore($asc->html(), $fewerRecruits, $moreRecruits);
    }
}
