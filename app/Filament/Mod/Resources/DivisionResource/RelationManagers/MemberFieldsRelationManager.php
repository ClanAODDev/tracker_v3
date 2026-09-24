<?php

namespace App\Filament\Mod\Resources\DivisionResource\RelationManagers;

use App\Filament\Forms\Components\DivisionMemberFieldForm;
use App\Models\DivisionMemberField;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class MemberFieldsRelationManager extends RelationManager
{
    protected static string $relationship = 'memberFields';

    protected static ?string $title = 'Member Fields';

    public function form(Schema $schema): Schema
    {
        return $schema->components(DivisionMemberFieldForm::schema());
    }

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('label')
            ->columns(DivisionMemberFieldForm::tableColumns())
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
