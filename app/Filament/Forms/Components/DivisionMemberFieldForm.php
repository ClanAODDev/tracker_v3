<?php

namespace App\Filament\Forms\Components;

use App\Enums\DivisionMemberFieldColor;
use App\Enums\DivisionMemberFieldType;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;

class DivisionMemberFieldForm
{
    public static function schema(): array
    {
        return [
            TextInput::make('label')
                ->required()
                ->maxLength(255),
            Select::make('type')
                ->options(DivisionMemberFieldType::options())
                ->default(DivisionMemberFieldType::TEXT->value)
                ->required()
                ->live(),
            Repeater::make('options')
                ->schema([
                    TextInput::make('value')
                        ->required()
                        ->maxLength(255),
                    Select::make('color')
                        ->options(DivisionMemberFieldColor::options())
                        ->default(DivisionMemberFieldColor::GRAY->value)
                        ->required(),
                ])
                ->columns(2)
                ->visible(fn (Get $get) => $get('type') === DivisionMemberFieldType::SELECT->value)
                ->required(fn (Get $get) => $get('type') === DivisionMemberFieldType::SELECT->value)
                ->minItems(1)
                ->helperText('The choices available for this select field, and the badge color shown for each on member listing tables.'),
            TextInput::make('display_order')
                ->numeric()
                ->default(0)
                ->required(),
            Toggle::make('filterable')
                ->default(true)
                ->helperText('Show a filter for this field on the member listing tables'),
            Toggle::make('self_editable')
                ->label('Allow Self-Editing')
                ->default(false)
                ->helperText('Let a member set their own value for this field from their profile page'),
        ];
    }

    public static function formatChoices(mixed $state): string
    {
        if (is_string($state)) {
            $state = json_decode($state, true) ?? [];
        }

        $values = collect($state)->pluck('value')->filter()->all();

        return $values ? implode(', ', $values) : '--';
    }

    /**
     * Table columns shared by every place a DivisionMemberField list is
     * shown (the per-division Mod relation manager, the clan-wide Admin
     * resource) — callers prepend/append their own context-specific columns.
     *
     * @return array<int, TextColumn|IconColumn>
     */
    public static function tableColumns(): array
    {
        return [
            TextColumn::make('label')
                ->searchable()
                ->sortable(),
            TextColumn::make('key')
                ->badge()
                ->color('gray'),
            TextColumn::make('type')
                ->badge(),
            TextColumn::make('options')
                ->label('Choices')
                ->formatStateUsing(fn (mixed $state) => self::formatChoices($state))
                ->wrap(),
            IconColumn::make('filterable')
                ->boolean(),
            IconColumn::make('self_editable')
                ->label('Self-Editable')
                ->boolean(),
        ];
    }
}
