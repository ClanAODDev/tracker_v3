<?php

namespace Tests\Browser;

use App\Note;
use App\User;
use Laravel\Dusk\Browser;
use Tests\Browser\Pages\MemberProfile;
use Tests\DuskTestCase;

class MemberNotesTest extends DuskTestCase
{

    /** @test */
    public function test_an_officer_can_see_notes()
    {
        $user = User::whereRoleId(2)->first();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->visit(new MemberProfile)
                ->assertVisible('.note');
        });
    }

    /** @test */
    public function test_a_member_cannot_see_notes()
    {
        $user = User::whereRoleId(1)->whereDeveloper(false)->first();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->visit(new MemberProfile())
                ->assertDontSee('notes');
        });
    }

    /** @test */
    public function test_a_user_can_create_notes()
    {
        $user = User::where('role_id', '>', 1)->first();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->visit(new MemberProfile)
                ->press('Add note')
                ->waitFor('#create-member-note')
                ->type('body', '--Example note--')
                ->select('type', 'misc')
                ->press('Submit');

            $browser->assertSeeIn('.note', '--Example note--');
            $browser->assertSeeIn('.note', 'NO TAG');
        });

        // cleanup created note
        $notes = Note::whereBody('--Example note--')->get();
        $notes->each->forceDelete();
    }
}
