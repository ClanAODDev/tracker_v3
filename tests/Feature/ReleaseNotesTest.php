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
        $this->assertStringContainsString('<strong>bold</strong>', $props['notes'][0]['html']);
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
            ->fillForm(['title' => 'October release (updated)'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('October release (updated)', $note->fresh()->title);
    }

    #[Test]
    public function the_existing_changelog_is_imported_as_an_entry(): void
    {
        $imported = ReleaseNote::where('title', 'Earlier changes')->first();

        $this->assertNotNull($imported);
        $this->assertStringContainsString('Markdown support added', $imported->body);
    }
}
