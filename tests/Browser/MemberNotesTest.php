<?php

namespace _\Tests\Browser;

use App\Note;
use App\User;
use Laravel\Dusk\Browser;
use Tests\Browser\Pages\MemberProfile;
use Tests\DuskTestCase;

class MemberNotesTest extends DuskTestCase
{

    /** @test */
    public function test_a_logged_in_user_can_see_notes()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                ->visit(new MemberProfile)
                ->assertVisible('.note');
        });
    }

    /** @test */
    public function test_a_user_can_create_notes()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
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
