<?php

namespace App\Filament\Admin\Resources\UserResource\Pages;

use App\Filament\Admin\Resources\UserResource;
use App\Support\Impersonation;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            Action::make('Impersonate')
                ->label('Impersonate')
                ->authorize('impersonate')
                ->action(function () {
                    Impersonation::start(auth()->user(), $this->record);

                    return redirect('/');
                }),
        ];
    }
}
