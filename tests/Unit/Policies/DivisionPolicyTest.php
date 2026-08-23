<?php

namespace Tests\Unit\Policies;

use App\Policies\DivisionPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;
use Tests\Traits\CreatesMembers;

class DivisionPolicyTest extends TestCase
{
    use CreatesDivisions;
    use CreatesMembers;
    use RefreshDatabase;

    private DivisionPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->policy = new DivisionPolicy;
    }

    #[Test]
    public function admin_cannot_delete_division()
    {
        $admin    = $this->createAdmin(userAttributes: ['developer' => false]);
        $division = $this->createActiveDivision();

        $this->assertFalse($this->policy->delete($admin, $division));
    }

    #[Test]
    public function admin_cannot_delete_any_division()
    {
        $admin = $this->createAdmin(userAttributes: ['developer' => false]);

        $this->assertFalse($this->policy->deleteAny($admin));
    }
}
