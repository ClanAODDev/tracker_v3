<?php

namespace Tests\Unit\Models;

use App\Models\Award;
use Illuminate\Foundation\Testing\RefreshDatabase;
use LogicException;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AwardNameImmutabilityTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function an_award_name_cannot_be_changed(): void
    {
        $award = Award::factory()->create(['name' => '5 Years of Service']);

        $this->expectException(LogicException::class);

        $award->update(['name' => 'Renamed']);
    }

    #[Test]
    public function other_fields_can_still_be_updated(): void
    {
        $award = Award::factory()->create(['name' => 'Original']);

        $award->update(['description' => 'New description', 'active' => false]);

        $this->assertSame('Original', $award->fresh()->name);
        $this->assertSame('New description', $award->fresh()->description);
    }

    #[Test]
    public function saving_an_unchanged_name_is_allowed(): void
    {
        $award = Award::factory()->create(['name' => 'Original']);

        $award->update(['name' => 'Original', 'description' => 'Changed']);

        $this->assertSame('Changed', $award->fresh()->description);
    }
}
