<?php

namespace App\Filament\Admin\Resources;

use App\Authorization\PermissionManager;
use App\Enums\Ability;
use App\Filament\Admin\Resources\AbilityAuditResource\Pages\ListAbilityAudits;
use App\Models\AbilityAudit;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class AbilityAuditResource extends Resource
{
    protected static ?string $model = AbilityAudit::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-shield-check';

    protected static string|UnitEnum|null $navigationGroup = 'Admin';

    protected static ?string $navigationLabel = 'Permission audit log';

    protected static ?string $modelLabel = 'permission change';

    public static function canAccess(): bool
    {
        return auth()->user()?->can(Ability::ManagePermissions) ?? false;
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')->label('When')->dateTime()->sortable(),
                TextColumn::make('actor.name')->label('Changed by'),
                TextColumn::make('action')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => self::actions()[$state] ?? $state)
                    ->color(fn (string $state) => str_ends_with($state, 'granted') ? 'success' : 'danger'),
                TextColumn::make('target')
                    ->label('Role or user')
                    ->state(fn (AbilityAudit $record) => $record->role?->getLabel() ?? $record->user?->name),
                TextColumn::make('ability')
                    ->formatStateUsing(fn (?string $state) => $state ? (Ability::tryFrom($state)?->label() ?? $state) : null),
                TextColumn::make('reason')->wrap(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('action')->options(self::actions()),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAbilityAudits::route('/'),
        ];
    }

    private static function actions(): array
    {
        return [
            PermissionManager::ROLE_GRANTED => 'Role granted',
            PermissionManager::ROLE_REVOKED => 'Role revoked',
            PermissionManager::USER_GRANTED => 'User granted',
            PermissionManager::USER_REVOKED => 'User revoked',
        ];
    }
}
