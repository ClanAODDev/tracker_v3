<?php

namespace App\Filament\Profile\Pages;

use App\Filament\Forms\Components\PartTimeDivisionsForm;
use Filament\Actions\Action;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class PartTimeDivisions extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationGroup = 'User';

    protected static ?string $navigationIcon = 'heroicon-o-puzzle-piece';

    protected static string $view = 'filament.profile.pages.part-time';

    public array $partTimeDivisions = [];

    public ?array $data = [];

    public function mount(): void
    {
        $selected = auth()->user()->member->partTimeDivisions()
            ->pluck('divisions.id')
            ->all();

        $this->form->fill([
            'partTimeDivisions' => $selected,
        ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('Save Part-Time Divisions')
                ->action('save')
                ->keyBindings(['mod+s']),
        ];
    }

    public function save(): void
    {
        try {
            $member = auth()->user()->member;
            $selected = PartTimeDivisionsForm::selectedFrom($this->data ?? []);
            PartTimeDivisionsForm::sync($member, $selected);

            Notification::make()->title('Settings updated successfully')->success()->send();
        } catch (\Throwable $e) {
            \Log::error('Part-time division save failed: ' . $e->getMessage());
            Notification::make()->title('Something went wrong while saving your settings.')->danger()->send();
        }
    }

    public function form(Form $form): Form
    {

        return $form
            ->schema([
                PartTimeDivisionsForm::makeUsingFormModel(),
            ])
            ->statePath('data');
    }
}
