<?php

namespace Tests\Feature\Authorization;

use App\Enums\DivisionMemberFieldType;
use App\Models\Award;
use App\Models\DivisionMemberField;
use App\Models\Ticket;
use App\Models\User;
use Laravel\Sanctum\NewAccessToken;
use PHPUnit\Framework\Attributes\Test;

class AccountPolicyMatrixTest extends PermissionMatrixTestCase
{
    private const IMPERSONATION_TARGETS = ['self', 'member', 'admin', 'developer'];

    #[Test]
    public function user_policy_matches_the_recorded_matrix(): void
    {
        $this->buildWorld();

        $checks = [];

        foreach (['viewAny', 'view', 'create', 'update', 'delete', 'restore', 'forceDelete', 'manageUnassigned', 'train'] as $ability) {
            $checks[] = [$ability, '(any user)', fn () => [User::class]];
        }

        $lines = $this->evaluate(PermissionWorld::ACTORS, $checks);

        foreach (['testing', 'production'] as $environment) {
            $original         = $this->app['env'];
            $this->app['env'] = $environment;

            foreach (self::IMPERSONATION_TARGETS as $target) {
                foreach (PermissionWorld::ACTORS as $actor) {
                    $user = $target === 'self' ? $this->world->users[$actor] : $this->world->users[$target];

                    $lines[] = self::line('impersonate', "{$target} ({$environment})", $actor, $this->gate($actor, 'impersonate', [$user->fresh()]));
                }
            }

            $this->app['env'] = $original;
        }

        $this->assertMatchesSnapshot('UserPolicy', $lines);
    }

    #[Test]
    public function ticket_policy_matches_the_recorded_matrix(): void
    {
        $this->buildWorld();

        $othersTicket = Ticket::factory()->create(['caller_id' => $this->world->users['admin']->id, 'division_id' => $this->world->divisionA->id]);

        $checks = [];

        foreach (['manage', 'viewAny', 'create', 'createComment', 'deleteComment'] as $ability) {
            $checks[] = [$ability, '(any ticket)', fn () => [Ticket::class]];
        }

        foreach (['view', 'update', 'delete', 'restore', 'forceDelete'] as $ability) {
            $checks[] = [$ability, 'own_ticket', fn (string $actor) => [Ticket::factory()->create(['caller_id' => $this->world->users[$actor]->id, 'division_id' => $this->world->divisionA->id])]];
            $checks[] = [$ability, 'others_ticket', fn () => [$othersTicket->fresh()]];
        }

        $this->assertMatchesSnapshot('TicketPolicy', $this->evaluate(PermissionWorld::ACTORS, $checks));
    }

    #[Test]
    public function division_member_field_policy_matches_the_recorded_matrix(): void
    {
        $this->buildWorld();

        $otherDivisionField = DivisionMemberField::create([
            'division_id' => $this->world->divisionB->id,
            'key'         => 'other_division_field',
            'label'       => 'other_division_field',
            'type'        => DivisionMemberFieldType::TEXT,
        ]);

        $checks = [
            ['viewAny', '(no division)', fn () => [DivisionMemberField::class]],
            ['viewAny', 'own_division', fn ()   => [DivisionMemberField::class, $this->world->divisionA->fresh()]],
            ['viewAny', 'other_division', fn () => [DivisionMemberField::class, $this->world->divisionB->fresh()]],
            ['create', '(no division)', fn ()   => [DivisionMemberField::class]],
            ['create', 'own_division', fn ()    => [DivisionMemberField::class, $this->world->divisionA->fresh()]],
            ['create', 'other_division', fn ()  => [DivisionMemberField::class, $this->world->divisionB->fresh()]],
        ];

        foreach (['update', 'delete'] as $ability) {
            $checks[] = [$ability, 'own_division_field', fn () => [$this->world->fields['self_editable_field']->fresh()]];
            $checks[] = [$ability, 'other_division_field', fn () => [$otherDivisionField->fresh()]];
        }

        $this->assertMatchesSnapshot('DivisionMemberFieldPolicy', $this->evaluate(PermissionWorld::ACTORS, $checks));
    }

    #[Test]
    public function api_token_policy_matches_the_recorded_matrix(): void
    {
        $this->buildWorld();

        $othersToken = $this->world->users['admin']->createToken('others')->accessToken;

        $checks = [
            ['create', '(any token)', fn () => [NewAccessToken::class]],
            ['destroy', 'own_token (model)', fn (string $actor) => [NewAccessToken::class, $this->world->users[$actor]->createToken('own')->accessToken]],
            ['destroy', 'others_token (model)', fn ()           => [NewAccessToken::class, $othersToken->fresh()]],
            ['destroy', 'own_token (id)', fn (string $actor)    => [NewAccessToken::class, $this->world->users[$actor]->createToken('own')->accessToken->id]],
            ['destroy', 'others_token (id)', fn ()              => [NewAccessToken::class, $othersToken->id]],
        ];

        $this->assertMatchesSnapshot('ApiTokenPolicy', $this->evaluate(PermissionWorld::ACTORS, $checks));
    }

    #[Test]
    public function award_policy_matches_the_recorded_matrix(): void
    {
        $this->buildWorld();

        $award  = Award::factory()->create();
        $checks = [
            ['deleteAny', '(any award)', fn () => [Award::class]],
            ['delete', 'an_award', fn () => [$award->fresh()]],
        ];

        $this->assertMatchesSnapshot('AwardPolicy', $this->evaluate(PermissionWorld::ACTORS, $checks));
    }
}
