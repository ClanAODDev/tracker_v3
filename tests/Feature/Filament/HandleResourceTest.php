<?php

namespace Tests\Feature\Filament;

use App\Filament\Admin\Resources\HandleResource\Pages\CreateHandle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\CreatesMembers;

class HandleResourceTest extends TestCase
{
    use CreatesMembers;
    use RefreshDatabase;

    #[Test]
    public function admin_can_create_a_handle_type_with_a_validation_regex(): void
    {
        $this->actingAs($this->createAdmin());

        Livewire::test(CreateHandle::class)
            ->fillForm([
                'label'      => 'Steam',
                'type'       => 'steam',
                'regex'      => '/^[0-9]+$/',
                'regex_hint' => 'Steam ID must be numeric.',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('handles', [
            'label'      => 'Steam',
            'regex'      => '/^[0-9]+$/',
            'regex_hint' => 'Steam ID must be numeric.',
        ]);
    }

    #[Test]
    public function admin_cannot_save_an_invalid_regex_pattern(): void
    {
        $this->actingAs($this->createAdmin());

        Livewire::test(CreateHandle::class)
            ->fillForm([
                'label' => 'Steam',
                'type'  => 'steam',
                'regex' => 'not a valid pattern [',
            ])
            ->call('create')
            ->assertHasFormErrors(['regex']);

        $this->assertDatabaseMissing('handles', ['label' => 'Steam']);
    }
}
