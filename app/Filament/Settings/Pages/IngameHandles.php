<?php

namespace App\Filament\Settings\Pages;

use App\Filament\Forms\Components\IngameHandlesForm;
use App\Models\Member;
use App\Services\MemberHandleService;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class IngameHandles extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static string $view = 'filament.settings.pages.profile';

    public ?Member $record = null;

    public array $formData = [];

    public function mount(): void
    {
        $this->record = auth()->user()->member;
        $this->formData['handleGroups'] = MemberHandleService::getGroupedHandles($this->record);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('Save Handles')->extraAttributes([
                'wire:click.prevent' => 'save',
            ])->submit('handles-form'),
        ];
    }

    public function save(): void
    {
        if (! $this->record) {
            Notification::make()
                ->title('Unable to save: No member record found.')
                ->danger()
                ->send();

            return;
        }

        // resource forms automatically validate, but we have explicitly call it here
        $this->form->validate();

        MemberHandleService::saveHandles($this->record, $this->formData['handleGroups']);

        Notification::make()
            ->title('Handles updated successfully')
            ->success()
            ->send();
    }

    public function form(Form $form): Form
    {
        return $form
            ->statePath('formData')
            ->schema([
                Forms\Components\Section::make('Handles')
                    ->columns(1)
                    ->schema([
                        IngameHandlesForm::make(),
                    ]),
            ]);
    }
}
