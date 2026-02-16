<?php

namespace Tests\Unit;

use App\Models\Division;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class DivisionTest extends TestCase
{
    #[Test]
    public function it_has_a_lowercase_abbreviation()
    {
        $division = Division::factory()->make([
            'abbreviation' => 'UPPERCASE',
            'handle_id'    => 1,
        ]);

        $this->assertSame($division->abbreviation, 'uppercase');
    }
}
