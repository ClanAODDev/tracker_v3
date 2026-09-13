<?php

namespace Tests\Unit\Support;

use App\Support\MonthlySeriesBuilder;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class MonthlySeriesBuilderTest extends TestCase
{
    #[Test]
    public function months_returns_one_entry_for_a_single_month_range()
    {
        $months = MonthlySeriesBuilder::months(
            Carbon::parse('2026-03-15'),
            Carbon::parse('2026-03-20'),
        );

        $this->assertCount(1, $months);
        $this->assertSame('2026-03', $months->first()['bucket']);
        $this->assertSame('Mar 26', $months->first()['date']);
    }

    #[Test]
    public function months_lists_every_month_in_an_inclusive_range()
    {
        $months = MonthlySeriesBuilder::months(
            Carbon::parse('2026-01-01'),
            Carbon::parse('2026-04-30'),
        );

        $this->assertSame(['2026-01', '2026-02', '2026-03', '2026-04'], $months->pluck('bucket')->all());
    }

    #[Test]
    public function months_caps_a_range_extending_into_the_future_at_the_current_month()
    {
        $this->travelTo(Carbon::parse('2026-06-15'), function () {
            $months = MonthlySeriesBuilder::months(
                Carbon::parse('2026-04-01'),
                Carbon::parse('2026-12-31'),
            );

            $this->assertSame(['2026-04', '2026-05', '2026-06'], $months->pluck('bucket')->all());
        });
    }

    #[Test]
    public function fill_zero_fills_months_missing_from_the_raw_rows()
    {
        $months = MonthlySeriesBuilder::months(
            Carbon::parse('2026-01-01'),
            Carbon::parse('2026-03-31'),
        );

        $rawRows = collect([
            (object) ['bucket' => '2026-01', 'recruits' => 4],
            (object) ['bucket' => '2026-03', 'recruits' => 2],
        ]);

        $filled = MonthlySeriesBuilder::fill($months, $rawRows, 'recruits');

        $this->assertSame([
            ['Jan 26', 4],
            ['Feb 26', 0],
            ['Mar 26', 2],
        ], $filled->values()->all());
    }

    #[Test]
    public function fill_returns_all_zeros_when_no_raw_rows_are_given()
    {
        $months = MonthlySeriesBuilder::months(
            Carbon::parse('2026-01-01'),
            Carbon::parse('2026-02-28'),
        );

        $filled = MonthlySeriesBuilder::fill($months, collect(), 'recruits');

        $this->assertSame([
            ['Jan 26', 0],
            ['Feb 26', 0],
        ], $filled->values()->all());
    }
}
