<?php

namespace Tests\Feature\Filament;

use App\Filament\Admin\Resources\DivisionResource;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;
use Tests\Traits\CreatesMembers;

class DivisionResourceTest extends TestCase
{
    use CreatesDivisions;
    use CreatesMembers;
    use RefreshDatabase;

    #[Test]
    public function plain_admin_cannot_bulk_delete_divisions()
    {
        $admin = $this->createAdmin(userAttributes: ['developer' => false]);

        $this->actingAs($admin);

        $this->assertFalse(DivisionResource::canDeleteAny());
    }

    #[Test]
    public function developer_can_bulk_delete_divisions()
    {
        $developer = $this->createAdmin();

        $this->actingAs($developer);

        $this->assertTrue(DivisionResource::canDeleteAny());
    }
}
