<?php

namespace Tests\Feature;

use Tests\TestCase;

class DivisionsTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function a_user_can_view_all_divisions()
    {
        $response = $this->get('/');

        $response->assertStatus(200);

        $response->assertSee('Planetside 2')
            ->assertSee('Battlefield');
    }
}
