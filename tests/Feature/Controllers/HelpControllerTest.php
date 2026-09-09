<?php

namespace Tests\Feature\Controllers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\CreatesMembers;

class HelpControllerTest extends TestCase
{
    use CreatesMembers;
    use RefreshDatabase;

    #[Test]
    public function general_page_renders_the_inertia_component()
    {
        $this->actingAs($this->createOfficer())
            ->get(route('help'))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page->component('help/index'));
    }

    #[Test]
    public function markdown_docs_render_converted_html()
    {
        $this->actingAs($this->createOfficer())
            ->get(route('help.managing-rank'))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('help/doc')
                ->where('title', 'Managing Rank')
                ->where('body', fn ($body) => str_contains($body, '<h1') && str_contains($body, 'Managing Rank')
                    && str_contains($body, '<li>')));
    }

    #[Test]
    public function admin_docs_require_the_admin_role()
    {
        $this->actingAs($this->createOfficer())
            ->get(route('help.admin.home'))
            ->assertForbidden();

        $this->actingAs($this->createAdmin())
            ->get(route('help.admin.home'))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('help/doc')
                ->where('eyebrow', 'Admin documentation'));
    }
}
