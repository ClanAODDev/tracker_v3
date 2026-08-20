<?php

namespace Tests\Unit\Enums;

use App\Enums\Rank;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RankTest extends TestCase
{
    #[Test]
    public function is_at_least_returns_correct_value()
    {
        $this->assertTrue(Rank::SERGEANT->isAtLeast(Rank::SERGEANT));
        $this->assertTrue(Rank::SERGEANT->isAtLeast(Rank::CORPORAL));
        $this->assertFalse(Rank::SERGEANT->isAtLeast(Rank::STAFF_SERGEANT));
    }

    #[Test]
    public function is_above_returns_correct_value()
    {
        $this->assertTrue(Rank::SERGEANT->isAbove(Rank::CORPORAL));
        $this->assertFalse(Rank::SERGEANT->isAbove(Rank::SERGEANT));
        $this->assertFalse(Rank::SERGEANT->isAbove(Rank::STAFF_SERGEANT));
    }

    #[Test]
    public function is_at_most_returns_correct_value()
    {
        $this->assertTrue(Rank::SERGEANT->isAtMost(Rank::SERGEANT));
        $this->assertTrue(Rank::SERGEANT->isAtMost(Rank::STAFF_SERGEANT));
        $this->assertFalse(Rank::SERGEANT->isAtMost(Rank::CORPORAL));
    }

    #[Test]
    public function is_below_returns_correct_value()
    {
        $this->assertTrue(Rank::CORPORAL->isBelow(Rank::SERGEANT));
        $this->assertFalse(Rank::SERGEANT->isBelow(Rank::SERGEANT));
        $this->assertFalse(Rank::STAFF_SERGEANT->isBelow(Rank::SERGEANT));
    }

    #[Test]
    public function forbidden_name_prefixes_excludes_trainer()
    {
        $this->assertNotContains('tr', Rank::forbiddenNamePrefixes());
    }

    #[Test]
    public function forbidden_name_prefixes_includes_other_rank_abbreviations()
    {
        $prefixes = Rank::forbiddenNamePrefixes();

        $this->assertContains('rct', $prefixes);
        $this->assertContains('sgt', $prefixes);
        $this->assertContains('sgtmaj', $prefixes);
        $this->assertCount(13, $prefixes);
    }
}
