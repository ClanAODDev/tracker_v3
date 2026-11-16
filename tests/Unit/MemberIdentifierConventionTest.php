<?php

namespace Tests\Unit;

use App\Models\Member;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use ReflectionClass;
use ReflectionMethod;
use ReflectionNamedType;
use Symfony\Component\Finder\Finder;
use Tests\TestCase;

class MemberIdentifierConventionTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function no_member_relation_is_keyed_on_the_forum_id()
    {
        $this->assertSame(
            [],
            $this->relationsKeyedOnClanId(),
            'A relation references members by `clan_id`. Point it at `members.id`; the forum ID is only for forum integration.'
        );
    }

    #[Test]
    public function new_member_ids_cannot_collide_with_forum_ids()
    {
        $member = Member::factory()->create();

        $this->assertGreaterThanOrEqual(1000000, $member->id);
    }

    private function relationsKeyedOnClanId(): array
    {
        $found = [];

        foreach ($this->modelClasses() as $class) {
            foreach ($this->relationMethods($class) as $method) {
                $relation = (new $class)->{$method}();

                if ($this->touchesMemberByClanId($relation)) {
                    $found[] = "{$class}::{$method}";
                }
            }
        }

        return $found;
    }

    private function touchesMemberByClanId(Relation $relation): bool
    {
        $involvesMember = $relation->getParent() instanceof Member || $relation->getRelated() instanceof Member;

        if (! $involvesMember) {
            return false;
        }

        $keys = collect(['getOwnerKeyName', 'getLocalKeyName', 'getForeignKeyName', 'getFirstKeyName', 'getSecondLocalKeyName'])
            ->filter(fn (string $getter) => method_exists($relation, $getter))
            ->map(fn (string $getter) => Str::afterLast((string) $relation->{$getter}(), '.'));

        return $keys->contains('clan_id');
    }

    private function modelClasses(): array
    {
        return collect(Finder::create()->files()->in(app_path('Models'))->name('*.php')->contains('/^(final |abstract )?class \\w+/m'))
            ->map(fn ($file) => 'App\\Models\\' . str_replace(['/', '.php'], ['\\', ''], $file->getRelativePathname()))
            ->filter(fn (string $class) => class_exists($class) && is_subclass_of($class, Model::class) && ! (new ReflectionClass($class))->isAbstract())
            ->values()
            ->all();
    }

    private function relationMethods(string $class): array
    {
        return collect((new ReflectionClass($class))->getMethods(ReflectionMethod::IS_PUBLIC))
            ->filter(function (ReflectionMethod $method) {
                $type = $method->getReturnType();

                return $method->getNumberOfRequiredParameters() === 0
                    && ! $method->isStatic()
                    && $type instanceof ReflectionNamedType
                    && is_a($type->getName(), Relation::class, true);
            })
            ->map(fn (ReflectionMethod $method) => $method->getName())
            ->unique()
            ->values()
            ->all();
    }
}
