<?php

namespace Tests\Unit;

use App\Models\Division;
use Tests\TestCase;

use Illuminate\Foundation\Testing\WithFaker;

class DivisionTest extends TestCase
{
    /** @test */
    public function it_has_a_lowercase_abbreviation()
    {
        $division = Division::factory(['abbreviation' => 'UPPERCASE'])->make();

        $this->assertEquals($division->abbreviation, 'uppercase');
    }
}
