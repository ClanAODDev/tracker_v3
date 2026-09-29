<?php

namespace Tests\Feature\Authorization;

use PHPUnit\Framework\Attributes\Test;
use Tests\Traits\CreatesPendingActionItems;

class PagePermissionMatrixTest extends PermissionMatrixTestCase
{
    use CreatesPendingActionItems;

    #[Test]
    public function division_pages_match_the_recorded_matrix(): void
    {
        $this->buildWorld();
        $this->createAllPendingActionItems($this->world->divisionA);

        $pages = [
            'division A show'    => fn () => route('division', $this->world->divisionA->slug),
            'division B show'    => fn () => route('division', $this->world->divisionB->slug),
            'division A members' => fn () => route('division.members', $this->world->divisionA->slug),
        ];

        $this->assertMatchesSnapshot('DivisionPages', $this->pageLines($pages));
    }

    #[Test]
    public function member_profile_pages_match_the_recorded_matrix(): void
    {
        $this->buildWorld();

        $lines = [];

        foreach (PermissionWorld::TARGETS as $target) {
            $lines = [...$lines, ...$this->pageLines([
                "profile of {$target}" => fn (string $actor) => ($member = $this->world->target($actor, $target)) ? route('member', $member->getUrlParams()) : null,
            ])];
        }

        $this->assertMatchesSnapshot('MemberProfilePages', $lines);
    }

    private function pageLines(array $pages): array
    {
        $lines = [];

        foreach ($pages as $page => $url) {
            foreach (PermissionWorld::ACTORS as $actor) {
                $resolved = $url($actor);

                if ($resolved === null) {
                    $lines[] = self::line($page, '(status)', $actor, 'n/a');

                    continue;
                }

                $response = $this->asActor($actor)->get($resolved);
                $lines[]  = self::line($page, '(status)', $actor, (string) $response->status());

                if ($response->status() !== 200) {
                    continue;
                }

                $props = $response->viewData('page')['props'];
                unset($props['auth'], $props['nav'], $props['flash']);

                foreach ($this->permissionFlags($props) as $path => $value) {
                    $lines[] = self::line($page, $path, $actor, $value);
                }

                foreach ($this->pendingActionKeys($props) as $path => $keys) {
                    $lines[] = self::line($page, $path, $actor, '[' . implode(', ', $keys) . ']');
                }
            }
        }

        return $lines;
    }

    private function permissionFlags(array $props, string $prefix = ''): array
    {
        $flags = [];

        foreach ($props as $key => $value) {
            $path = $prefix === '' ? (string) $key : (is_int($key) ? "{$prefix}[]" : "{$prefix}.{$key}");

            if (is_array($value)) {
                foreach ($this->permissionFlags($value, $path) as $childPath => $childValue) {
                    $flags[$childPath] = isset($flags[$childPath]) && $flags[$childPath] !== $childValue ? 'mixed' : $childValue;
                }

                continue;
            }

            if (is_bool($value) && is_string($key) && str_starts_with($key, 'can')) {
                $flags[$path] = isset($flags[$path]) && $flags[$path] !== ($value ? 'true' : 'false') ? 'mixed' : ($value ? 'true' : 'false');
            }
        }

        ksort($flags);

        return $flags;
    }

    private function pendingActionKeys(array $props, string $prefix = ''): array
    {
        $found = [];

        foreach ($props as $key => $value) {
            if (! is_array($value)) {
                continue;
            }

            $path = $prefix === '' ? (string) $key : "{$prefix}.{$key}";

            if ($key === 'pendingActions') {
                $found[$path] = collect($value)->pluck('key')->filter()->sort()->values()->all();

                continue;
            }

            $found = [...$found, ...$this->pendingActionKeys($value, $path)];
        }

        return $found;
    }
}
