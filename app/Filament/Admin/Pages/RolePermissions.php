<?php

namespace App\Filament\Admin\Pages;

use App\Authorization\PermissionManager;
use App\Enums\Ability;
use App\Enums\Role;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;
use UnitEnum;

class RolePermissions extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-key';

    protected static string|UnitEnum|null $navigationGroup = 'Admin';

    protected static ?string $navigationLabel = 'Role permissions';

    protected static ?string $title = 'Role permissions';

    public ?array $data = [];

    public static function canAccess(): bool
    {
        return auth()->user()?->can(Ability::ManagePermissions) ?? false;
    }

    public function mount(): void
    {
        $this->loadRole(Role::OFFICER);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('role')
                    ->options(collect(Role::cases())->mapWithKeys(fn (Role $role) => [$role->value => $role->getLabel()]))
                    ->selectablePlaceholder(false)
                    ->helperText('Switching roles discards unsaved changes.')
                    ->live()
                    ->afterStateUpdated(fn ($state) => $this->loadRole(Role::from((int) $state))),
                ...collect(self::areas())->map(fn (array $abilities, string $area) => Section::make($area)
                    ->schema([
                        CheckboxList::make('abilities.' . Str::slug($area))
                            ->hiddenLabel()
                            ->options(collect($abilities)->mapWithKeys(fn (Ability $ability) => [$ability->value => $ability->label()]))
                            ->descriptions(fn (Get $get) => $this->descriptionsFor($abilities, Role::from((int) $get('role'))))
                            ->disableOptionWhen(fn (Get $get, string $value) => (int) $get('role') === Role::ADMIN->value && $value === Ability::ManagePermissions->value)
                            ->bulkToggleable()
                            ->columns(2),
                    ]))
                    ->values()
                    ->all(),
            ])
            ->statePath('data');
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            Form::make([EmbeddedSchema::make('form')])
                ->id('form')
                ->footer([Actions::make([$this->saveAction()])]),
        ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('reset')
                ->label('Reset to defaults')
                ->color('gray')
                ->requiresConfirmation()
                ->modalDescription(fn () => 'Restore the default permissions for ' . $this->currentRole()->getLabel() . '.')
                ->schema([Textarea::make('reason')->required()->maxLength(255)])
                ->action(function (array $data) {
                    $role    = $this->currentRole();
                    $changes = app(PermissionManager::class)->resetRole($role, auth()->user(), $data['reason']);
                    $this->loadRole($role);

                    Notification::make()->success()->title("{$role->getLabel()} reset to defaults ({$changes} changes)")->send();
                }),
        ];
    }

    public function saveAction(): Action
    {
        return Action::make('save')
            ->label('Save changes')
            ->modalHeading('Save permission changes')
            ->modalDescription('Every change is recorded in the permissions audit log.')
            ->schema([Textarea::make('reason')->required()->maxLength(255)])
            ->action(function (array $data) {
                $state     = $this->form->getState();
                $role      = Role::from((int) $state['role']);
                $abilities = collect($state['abilities'] ?? [])->flatten()->map(fn ($value) => Ability::tryFrom($value))->filter()->all();
                $changes   = app(PermissionManager::class)->setRoleAbilities($role, $abilities, auth()->user(), $data['reason']);
                $this->loadRole($role);

                Notification::make()->success()->title("Saved {$changes} " . Str::plural('change', $changes) . " for {$role->getLabel()}")->send();
            });
    }

    private function loadRole(Role $role): void
    {
        $current = collect(app(PermissionManager::class)->abilitiesFor($role));

        $this->form->fill([
            'role'      => $role->value,
            'abilities' => collect(self::areas())
                ->mapWithKeys(fn (array $abilities, string $area) => [
                    Str::slug($area) => collect($abilities)->filter(fn (Ability $ability) => $current->contains($ability))->map->value->values()->all(),
                ])
                ->all(),
        ]);
    }

    private function currentRole(): Role
    {
        return Role::from((int) ($this->data['role'] ?? Role::OFFICER->value));
    }

    private function descriptionsFor(array $abilities, Role $role): array
    {
        $defaults = app(PermissionManager::class)->defaultsFor($role);

        return collect($abilities)->mapWithKeys(fn (Ability $ability) => [
            $ability->value => $ability->description() . ' · Default: ' . (in_array($ability, $defaults, true) ? 'on' : 'off'),
        ])->all();
    }

    private static function areas(): array
    {
        return collect(Ability::cases())->groupBy(fn (Ability $ability) => $ability->area())->map->all()->all();
    }
}
