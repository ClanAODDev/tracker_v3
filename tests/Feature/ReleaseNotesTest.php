<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Filament\Admin\Resources\ReleaseNoteResource\Pages\CreateReleaseNote;
use App\Filament\Admin\Resources\ReleaseNoteResource\Pages\EditReleaseNote;
use App\Filament\Admin\Resources\ReleaseNoteResource\Pages\ListReleaseNotes;
use App\Models\ReleaseNote;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;
use Tests\Traits\CreatesMembers;

class ReleaseNotesTest extends TestCase
{
    use CreatesDivisions;
    use CreatesMembers;
    use RefreshDatabase;

    #[Test]
    public function the_page_lists_published_notes_newest_first_and_hides_drafts_and_scheduled_ones(): void
    {
        ReleaseNote::query()->delete();

        $older = ReleaseNote::factory()->create(['title' => 'Older release', 'published_at' => now()->subWeek()]);
        $newer = ReleaseNote::factory()->create(['title' => 'Newer release', 'body' => '**bold** change', 'published_at' => now()->subDay()]);
        ReleaseNote::factory()->draft()->create(['title' => 'Draft release']);
        ReleaseNote::factory()->scheduled()->create(['title' => 'Scheduled release']);

        $response = $this->actingAs($this->createOfficer())->get(route('changelog'))->assertOk();

        $props = $response->viewData('page')['props'];

        $this->assertSame(['Newer release', 'Older release'], collect($props['notes'])->pluck('title')->all());
        $this->assertSame('bold change', $props['notes'][0]['excerpt']);
        $this->assertSame(route('changelog.show', $newer->slug), $props['notes'][0]['url']);
        $this->assertArrayNotHasKey('html', $props['notes'][0]);
        $this->assertSame(2, $props['pagination']['total']);
    }

    #[Test]
    public function the_page_is_paginated(): void
    {
        ReleaseNote::query()->delete();

        ReleaseNote::factory()->count(12)->create();

        $props = $this->actingAs($this->createOfficer())->get(route('changelog'))->viewData('page')['props'];

        $this->assertCount(10, $props['notes']);
        $this->assertNotNull($props['pagination']['nextUrl']);
        $this->assertNull($props['pagination']['prevUrl']);
    }

    #[Test]
    public function guests_cannot_read_release_notes(): void
    {
        $this->get(route('changelog'))->assertRedirect();
    }

    #[Test]
    public function pending_registrations_are_sent_to_the_pending_page(): void
    {
        $pending = User::factory()->create(['member_id' => null, 'discord_id' => '123456789012345678']);

        $this->actingAs($pending)->get(route('changelog'))->assertRedirect(route('auth.discord.pending'));
    }

    #[Test]
    public function banned_users_cannot_read_release_notes(): void
    {
        $member = $this->createMember(['division_id' => $this->createActiveDivision()->id]);
        $banned = User::factory()->create(['member_id' => $member->id, 'name' => $member->name, 'role' => Role::BANNED]);

        $this->actingAs($banned)->get(route('changelog'))->assertForbidden();
    }

    #[Test]
    public function the_navigation_links_to_release_notes(): void
    {
        $props = $this->actingAs($this->createOfficer())->get(route('home'))->viewData('page')['props'];

        $this->assertContains('Release Notes', collect($props['nav'])->pluck('label')->all());
    }

    #[Test]
    public function an_admin_can_create_a_draft_then_publish_it_and_edit_it(): void
    {
        Filament::setCurrentPanel('admin');
        $this->actingAs($this->createAdmin());

        Livewire::test(CreateReleaseNote::class)
            ->fillForm(['title' => 'October release', 'version' => '4.0', 'body' => 'Notes'])
            ->call('create')
            ->assertHasNoFormErrors();

        $note = ReleaseNote::where('title', 'October release')->firstOrFail();
        $this->assertSame('draft', $note->status());

        Livewire::test(ListReleaseNotes::class)
            ->assertCanSeeTableRecords([$note])
            ->callTableAction('publish', $note);

        $this->assertSame('published', $note->fresh()->status());

        Livewire::test(EditReleaseNote::class, ['record' => $note->getRouteKey()])
            ->fillForm(['title' => 'October release (updated)', 'slug' => 'october'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('October release (updated)', $note->fresh()->title);
        $this->assertSame('october', $note->fresh()->slug);

        Livewire::test(EditReleaseNote::class, ['record' => $note->getRouteKey()])
            ->fillForm(['slug' => ''])
            ->call('save')
            ->assertHasFormErrors(['slug' => 'required']);
    }

    #[Test]
    public function a_note_has_its_own_page_with_the_full_text_and_neighbour_links(): void
    {
        ReleaseNote::query()->delete();

        $oldest = ReleaseNote::factory()->create(['title' => 'First release', 'published_at' => now()->subDays(3)]);
        $middle = ReleaseNote::factory()->create(['title' => 'Middle release', 'body' => "# Heading\n\n**bold** change", 'published_at' => now()->subDays(2)]);
        $newest = ReleaseNote::factory()->create(['title' => 'Newest release', 'published_at' => now()->subDay()]);

        $props = $this->actingAs($this->createOfficer())->get(route('changelog.show', $middle->slug))->assertOk()->viewData('page')['props'];

        $this->assertSame('Middle release', $props['note']['title']);
        $this->assertStringContainsString('<strong>bold</strong>', $props['note']['html']);
        $this->assertSame('Newest release', $props['newer']['title']);
        $this->assertSame(route('changelog.show', $newest->slug), $props['newer']['url']);
        $this->assertSame('First release', $props['older']['title']);
        $this->assertSame(route('changelog'), $props['indexUrl']);

        $edge = $this->get(route('changelog.show', $oldest->slug))->viewData('page')['props'];
        $this->assertNull($edge['older']);
    }

    #[Test]
    public function drafts_and_scheduled_notes_have_no_public_page(): void
    {
        $draft     = ReleaseNote::factory()->draft()->create();
        $scheduled = ReleaseNote::factory()->scheduled()->create();
        $officer   = $this->createOfficer();

        $this->actingAs($officer)->get(route('changelog.show', $draft->slug))->assertNotFound();
        $this->actingAs($officer)->get(route('changelog.show', $scheduled->slug))->assertNotFound();
    }

    #[Test]
    public function guests_cannot_open_a_note(): void
    {
        $note = ReleaseNote::factory()->create();

        $this->get(route('changelog.show', $note->slug))->assertRedirect();
    }

    #[Test]
    public function slugs_are_generated_from_the_title_and_stay_unique(): void
    {
        $first  = ReleaseNote::factory()->create(['title' => 'October Release']);
        $second = ReleaseNote::factory()->create(['title' => 'October Release']);
        $custom = ReleaseNote::factory()->create(['title' => 'Anything', 'slug' => 'my-own-slug']);

        $this->assertSame('october-release', $first->slug);
        $this->assertSame('october-release-2', $second->slug);
        $this->assertSame('my-own-slug', $custom->slug);
    }

    #[Test]
    public function admins_get_links_to_edit_and_manage_release_notes(): void
    {
        $note = ReleaseNote::factory()->create(['published_at' => now()->subDay()]);

        $this->actingAs($this->createAdmin());

        $this->assertSame(
            route('filament.admin.resources.release-notes.edit', $note),
            $this->get(route('changelog.show', $note->slug))->viewData('page')['props']['editUrl'],
        );
        $this->assertSame(
            route('filament.admin.resources.release-notes.index'),
            $this->get(route('changelog'))->viewData('page')['props']['manageUrl'],
        );
    }

    #[Test]
    public function non_admins_get_no_edit_or_manage_links(): void
    {
        $note = ReleaseNote::factory()->create(['published_at' => now()->subDay()]);

        $this->actingAs($this->createOfficer());

        $this->assertNull($this->get(route('changelog.show', $note->slug))->viewData('page')['props']['editUrl']);
        $this->assertNull($this->get(route('changelog'))->viewData('page')['props']['manageUrl']);
    }
}
