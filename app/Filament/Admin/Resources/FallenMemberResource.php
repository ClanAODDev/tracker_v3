<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\FallenMemberResource\Pages\CreateFallenMember;
use App\Filament\Admin\Resources\FallenMemberResource\Pages\EditFallenMember;
use App\Filament\Admin\Resources\FallenMemberResource\Pages\ListFallenMembers;
use App\Models\FallenMember;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\TextInputColumn;
use Filament\Tables\Table;

class FallenMemberResource extends Resource
{
    protected static ?string $model = FallenMember::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-heart';

    protected static string|\UnitEnum|null $navigationGroup = 'Members';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(191),
                Select::make('member_id')
                    ->relationship('member', 'name')
                    ->label('Linked Member')
                    ->helperText('Optional — leave blank if the member record no longer exists')
                    ->searchable()
                    ->nullable(),
                DatePicker::make('date_fallen')
                    ->nullable(),
                TextInput::make('forum_profile')
                    ->label('Forum Profile URL')
                    ->url()
                    ->maxLength(255)
                    ->nullable(),
                TextInput::make('display_order')
                    ->required()
                    ->numeric()
                    ->default(100),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('member.name')
                    ->label('Linked Member')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('date_fallen')
                    ->date()
                    ->placeholder('Unknown')
                    ->sortable(),
                TextInputColumn::make('display_order')
                    ->rules(['required', 'numeric'])
                    ->sortable()
                    ->width('80px'),
            ])
            ->defaultSort('display_order')
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListFallenMembers::route('/'),
            'create' => CreateFallenMember::route('/create'),
            'edit'   => EditFallenMember::route('/{record}/edit'),
        ];
    }
}
