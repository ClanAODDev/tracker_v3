<?php

namespace App\Filament\Mod\Resources\DivisionResource\RelationManagers;

use App\Enums\DivisionMemberFieldColor;
use App\Enums\DivisionMemberFieldType;
use App\Models\DivisionMemberField;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MemberFieldsRelationManager extends RelationManager
{
    protected static string $relationship = 'memberFields';

    protected static ?string $title = 'Member Fields';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
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
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->reorderable('display_order')
            ->defaultSort('display_order')
            ->columns([
                TextColumn::make('label')
                    ->searchable(),
                TextColumn::make('key')
                    ->badge()
                    ->color('gray'),
                TextColumn::make('type')
                    ->badge(),
                TextColumn::make('options')
                    ->label('Choices')
                    ->formatStateUsing(function (mixed $state): string {
                        if (is_string($state)) {
                            $state = json_decode($state, true) ?? [];
                        }

                        $values = collect($state)->pluck('value')->filter()->all();

                        return $values ? implode(', ', $values) : '--';
                    })
                    ->wrap(),
                IconColumn::make('filterable')
                    ->boolean(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->visible(fn () => auth()->user()->can('create', [DivisionMemberField::class, $this->getOwnerRecord()]))
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['key'] = (string) str($data['label'])->slug('_');

                        return $data;
                    }),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
