<?php

namespace Tests\Feature\Authorization;

use App\Enums\Rank;
use App\Models\TicketType;
use App\Support\Navigation;
use Filament\Facades\Filament;
use PHPUnit\Framework\Attributes\Test;

class AccessSurfaceMatrixTest extends PermissionMatrixTestCase
{
    #[Test]
    public function access_surfaces_match_the_recorded_matrix(): void
    {
        $this->buildWorld();

        TicketType::factory()->create(['name' => 'Sergeant-level help', 'minimum_rank' => Rank::SERGEANT]);

        $lines = [];

        foreach (PermissionWorld::ACTORS as $actor) {
            $user = $this->world->users[$actor];
            $this->actAs($actor);

            foreach (['mod', 'admin'] as $panel) {
                $lines[] = self::line('canAccessPanel', $panel, $actor, $this->outcome(fn () => $user->canAccessPanel(Filament::getPanel($panel))));
            }

            $lines[] = self::line('navigation', '(labels)', $actor, $this->listOutcome(fn () => $this->navigationLabels(Navigation::for($user))));
        }

        foreach (PermissionWorld::ACTORS as $actor) {
            $response = $this->homeResponse($actor);
            $page     = $response->status() === 200 ? $response->viewData('page') : null;
            $lines[]  = self::line('GET home', '(status)', $actor, (string) $response->status());

            foreach ($page['props']['auth']['permissions'] ?? [] as $flag => $value) {
                $lines[] = self::line('shared permission', $flag, $actor, $value ? 'true' : 'false');
            }
        }

        $this->assertMatchesSnapshot('AccessSurfaces', $lines);
    }

    private function homeResponse(string $actor)
    {
        return $this->asActor($actor)->get(route('home'));
    }

    private function navigationLabels(array $items, string $prefix = ''): array
    {
        $labels = [];

        foreach ($items as $item) {
            $label    = $prefix . (($item['type'] ?? null) === 'heading' ? '# ' : '') . $item['label'];
            $labels[] = $label;

            if (isset($item['children'])) {
                $labels = [...$labels, ...$this->navigationLabels($item['children'], "{$item['label']} > ")];
            }
        }

        return $labels;
    }

    private function listOutcome(callable $resolve): string
    {
        try {
            return '[' . implode(', ', $resolve()) . ']';
        } catch (\Throwable $e) {
            return 'error:' . class_basename($e);
        }
    }
}
