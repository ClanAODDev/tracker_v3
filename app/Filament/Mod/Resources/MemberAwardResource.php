<?php

namespace App\Filament\Mod\Resources;

use App\Filament\Mod\Resources\MemberAwardResource\Pages;
use App\Models\MemberAward;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class MemberAwardResource extends Resource
{
    protected static ?string $model = MemberAward::class;

    protected static ?string $navigationIcon = 'heroicon-o-trophy';

    protected static ?string $navigationGroup = 'Division';

    protected static ?string $navigationParentItem = 'Members';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('award_id')
                    ->relationship('award', 'name'),

                Forms\Components\Select::make('member_id')
                    ->searchable()
                    ->relationship('member', 'name'),

                Forms\Components\Textarea::make('reason')
                    ->columnSpanFull()
                    ->maxLength(191)
                    ->default(null),

                Forms\Components\Toggle::make('approved'),

                Forms\Components\Section::make('Metadata')->schema([
                    Forms\Components\DateTimePicker::make('created_at')->default(now()),
                    Forms\Components\DateTimePicker::make('updated_at')->default(now()),
                ])->columns(),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('award.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('member.name')->searchable(),
                Tables\Columns\TextColumn::make('reason')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('division.name')
                    ->sortable(),

                Tables\Columns\ToggleColumn::make('approved'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\Filter::make('needs approval')
                    ->query(fn (Builder $query): Builder => $query->where('approved', false))->default(),
                SelectFilter::make('by division')->relationship('division', 'name'),
                SelectFilter::make('by award')->relationship('award', 'name'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMemberAwards::route('/'),
            'create' => Pages\CreateMemberAward::route('/create'),
            'edit' => Pages\EditMemberAward::route('/{record}/edit'),
        ];
    }
}
