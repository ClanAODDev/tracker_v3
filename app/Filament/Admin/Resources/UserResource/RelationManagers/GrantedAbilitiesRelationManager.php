<?php

namespace App\Filament\Admin\Resources\UserResource\RelationManagers;

use App\Authorization\PermissionManager;
use App\Enums\Ability;
use App\Models\UserAbility;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class GrantedAbilitiesRelationManager extends RelationManager
{
    protected static string $relationship = 'grantedAbilities';

    protected static ?string $title = 'Granted abilities';

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return auth()->user()?->can(Ability::ManagePermissions) ?? false;
    }

    public function isReadOnly(): bool
    {
        return false;
    }

    public function table(Table $table): Table
    {
        return $table
            ->description('Abilities this user holds in addition to their role. Grants only add access; they never remove anything the role allows.')
            ->columns([
                TextColumn::make('ability')
                    ->formatStateUsing(fn (string $state) => Ability::tryFrom($state)?->label() ?? "{$state} (no longer exists)")
                    ->description(fn (UserAbility $record) => Ability::tryFrom($record->ability)?->description()),
                TextColumn::make('reason')->wrap(),
                TextColumn::make('grantedBy.name')->label('Granted by'),
                TextColumn::make('created_at')->label('Granted')->since()->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->headerActions([
                Action::make('grant')
                    ->label('Grant ability')
                    ->icon('heroicon-o-plus')
                    ->schema([
                        Select::make('ability')
                            ->options(fn () => $this->grantableOptions())
                            ->searchable()
                            ->required(),
                        Textarea::make('reason')->required()->maxLength(255),
                    ])
                    ->action(function (array $data) {
                        $ability = Ability::from($data['ability']);
                        app(PermissionManager::class)->grant($this->getOwnerRecord(), $ability, auth()->user(), $data['reason']);

                        Notification::make()->success()->title("Granted {$ability->label()}")->send();
                    }),
            ])
            ->recordActions([
                Action::make('revoke')
                    ->color('danger')
                    ->icon('heroicon-o-x-mark')
                    ->requiresConfirmation()
                    ->schema([Textarea::make('reason')->required()->maxLength(255)])
                    ->action(function (UserAbility $record, array $data) {
                        app(PermissionManager::class)->revoke($record, auth()->user(), $data['reason']);

                        Notification::make()->success()->title('Grant revoked')->send();
                    }),
            ]);
    }

    private function grantableOptions(): array
    {
        $user     = $this->getOwnerRecord();
        $granted  = $user->grantedAbilities()->pluck('ability')->all();
        $fromRole = $user->getRole() ? app(PermissionManager::class)->abilitiesFor($user->getRole()) : [];

        return collect(Ability::cases())
            ->reject(fn (Ability $ability) => in_array($ability->value, $granted, true))
            ->groupBy(fn (Ability $ability) => $ability->area())
            ->map(fn ($abilities) => $abilities->mapWithKeys(fn (Ability $ability) => [
                $ability->value => $ability->label() . (in_array($ability, $fromRole, true) ? ' (role already has it)' : ''),
            ])->all())
            ->all();
    }
}
