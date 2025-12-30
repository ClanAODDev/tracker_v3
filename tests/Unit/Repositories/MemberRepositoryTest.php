<?php

namespace Tests\Unit\Repositories;

use App\Repositories\MemberRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;
use Tests\Traits\CreatesMembers;

class MemberRepositoryTest extends TestCase
{
    use CreatesDivisions;
    use CreatesMembers;
    use RefreshDatabase;

    private MemberRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new MemberRepository;
    }

    public function test_search_finds_member_by_name()
    {
        $division = $this->createActiveDivision();
        $member = $this->createMember(['name' => 'TestMember', 'division_id' => $division->id]);

        $results = $this->repository->search('TestMember');

        $this->assertCount(1, $results);
        $this->assertEquals($member->id, $results->first()->id);
    }

    public function test_search_finds_member_by_partial_name()
    {
        $division = $this->createActiveDivision();
        $this->createMember(['name' => 'JohnDoe', 'division_id' => $division->id]);

        $results = $this->repository->search('John');

        $this->assertCount(1, $results);
    }

    public function test_search_with_backslash_does_not_throw_error()
    {
        $division = $this->createActiveDivision();
        $this->createMember(['name' => 'TestMember', 'division_id' => $division->id]);

        $results = $this->repository->search('test\\name');

        $this->assertCount(0, $results);
    }

    public function test_search_with_percent_does_not_match_wildcard()
    {
        $division = $this->createActiveDivision();
        $this->createMember(['name' => 'Alice', 'division_id' => $division->id]);
        $this->createMember(['name' => 'Bob', 'division_id' => $division->id]);

        $results = $this->repository->search('%');

        $this->assertCount(0, $results);
    }

    public function test_search_with_underscore_does_not_match_single_char_wildcard()
    {
        $division = $this->createActiveDivision();
        $this->createMember(['name' => 'Cat', 'division_id' => $division->id]);
        $this->createMember(['name' => 'Car', 'division_id' => $division->id]);

        $results = $this->repository->search('Ca_');

        $this->assertCount(0, $results);
    }

    public function test_search_finds_member_with_literal_special_chars_in_name()
    {
        $division = $this->createActiveDivision();
        $this->createMember(['name' => 'Test_Member', 'division_id' => $division->id]);

        $results = $this->repository->search('Test_Member');

        $this->assertCount(1, $results);
    }

    public function test_autocomplete_finds_member_by_name()
    {
        $division = $this->createActiveDivision();
        $member = $this->createMember(['name' => 'SearchableUser', 'division_id' => $division->id]);

        $results = $this->repository->searchAutocomplete('Searchable');

        $this->assertCount(1, $results);
        $this->assertEquals($member->clan_id, $results->first()['id']);
        $this->assertEquals('SearchableUser', $results->first()['label']);
    }

    public function test_autocomplete_with_backslash_does_not_throw_error()
    {
        $division = $this->createActiveDivision();
        $this->createMember(['name' => 'TestUser', 'division_id' => $division->id]);

        $results = $this->repository->searchAutocomplete('test\\query');

        $this->assertCount(0, $results);
    }

    public function test_autocomplete_with_percent_does_not_match_all()
    {
        $division = $this->createActiveDivision();
        $this->createMember(['name' => 'User1', 'division_id' => $division->id]);
        $this->createMember(['name' => 'User2', 'division_id' => $division->id]);

        $results = $this->repository->searchAutocomplete('%');

        $this->assertCount(0, $results);
    }

    public function test_autocomplete_respects_limit()
    {
        $division = $this->createActiveDivision();
        for ($i = 1; $i <= 10; $i++) {
            $this->createMember(['name' => "TestUser{$i}", 'division_id' => $division->id]);
        }

        $results = $this->repository->searchAutocomplete('TestUser', 3);

        $this->assertCount(3, $results);
    }
}
