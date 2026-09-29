<?php

namespace Tests\Feature\Authorization;

use App\Enums\Role;
use Closure;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;
use Throwable;

abstract class PermissionMatrixTestCase extends TestCase
{
    use RefreshDatabase;

    protected PermissionWorld $world;

    protected function buildWorld(Role $unitLeaderRole = Role::OFFICER): void
    {
        $this->world = PermissionWorld::build($unitLeaderRole);
    }

    protected function actAs(string $actor): void
    {
        $user = $this->world->users[$actor];

        $this->actingAs($user);
        session()->forget('impersonatingRole');

        if ($role = $this->world->impersonatedRole($actor)) {
            session(['impersonatingRole' => $role->value]);
        }
    }

    protected function gate(string $actor, string $ability, array $arguments): string
    {
        $this->actAs($actor);

        return $this->outcome(fn () => Gate::forUser($this->world->users[$actor])->inspect($ability, $arguments)->allowed());
    }

    protected function outcome(Closure $check): string
    {
        try {
            return $check() ? 'allow' : 'deny';
        } catch (Throwable $e) {
            return 'error:' . class_basename($e);
        }
    }

    protected function assertMatchesSnapshot(string $name, array $lines): void
    {
        $path   = __DIR__ . "/snapshots/{$name}.txt";
        $actual = implode("\n", $lines) . "\n";

        if (getenv('UPDATE_PERMISSION_SNAPSHOTS')) {
            @mkdir(dirname($path), 0755, true);
            file_put_contents($path, $actual);
        }

        $this->assertFileExists($path, "No snapshot for {$name}. Review current behaviour, then run with UPDATE_PERMISSION_SNAPSHOTS=1 to record it.");
        $this->assertSame(file_get_contents($path), $actual, "Permission matrix for {$name} changed. If intentional, record it in docs/authorization-and-org-hierarchy-plan.md and regenerate with UPDATE_PERMISSION_SNAPSHOTS=1.");
    }

    protected static function line(string $ability, string $target, string $actor, string $result): string
    {
        return sprintf('%-24s | %-40s | %-24s | %s', $ability, $target, $actor, $result);
    }
}
