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
    public function officers_can_see_notes()
    {
        $user = User::where('role_id', '>', 1)->first();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->visit(new MemberProfile)
                ->assertVisible('.note');
        });
    }

    /** @test */
    public function members_cannot_see_notes()
    {
        $user = User::whereRoleId(1)->whereDeveloper(false)->first();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->visit(new MemberProfile())
                ->assertVisible('.notes-hidden');
        });
    }

    /** @test */
    public function officers_and_above_can_create_notes()
    {
        $user = User::where('role_id', '>', 1)->first();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->visit(new MemberProfile)
                ->press('Actions')
                ->click('.btn-add-note')
                ->waitFor('#create-member-note')
                ->click('.select2-selection__rendered')
                ->click('.select2-results__option')
                ->type('body', '--Example note--')
                ->select('type', 'misc')
                ->press('Submit');

            $browser->assertSeeIn('.note', '--Example note--');
            $browser->assertSeeIn('.note', 'COC');
        });

        // cleanup created note
        $notes = Note::whereBody('--Example note--')->get();
        $notes->each->forceDelete();
    }
}
