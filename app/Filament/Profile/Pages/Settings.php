<?php

namespace App\Filament\Profile\Pages;

use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithRecord;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class Settings extends Page implements HasForms
{
    use InteractsWithForms, InteractsWithRecord;

    protected static ?string $navigationGroup = 'User';

    protected static ?string $navigationIcon = 'heroicon-o-cog';

    protected static string $view = 'filament.profile.pages.settings';

    public array $settings = [];

    public function mount(): void
    {
        $this->settings = auth()->user()->settings;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('Save Settings')
                ->action('save')
                ->keyBindings(['mod+s']),
        ];
    }

    public function save(): void
    {
        try {
            $this->form->validate();

            $user = auth()->user();
            $user->settings = array_merge($user->defaultSettings, $this->settings);
            $user->save();

        } catch (\Exception $exception) {
            Notification::make()
                ->title('Something went wrong while saving your settings.')
                ->danger()
                ->send();
            \Log::error($exception->getMessage());
        }

        Notification::make()
            ->title('Settings updated successfully')
            ->success()
            ->send();
    }

    public function form(Form $form): Form
    {
        return $form
            ->statePath('settings')
            ->schema([

                Forms\Components\Section::make('Misc')
                    ->schema([
                        Forms\Components\Select::make('snow')
                            ->selectablePlaceholder(false)
                            ->hintIcon('heroicon-o-cloud')
                            ->options([
                                'no_snow' => 'None',
                                'some_snow' => 'Some snow',
                                'all_the_snow' => 'All the snow',
                            ])
                            ->label('Enable snow effect on profile (holiday season)'),
                    ]),

                Forms\Components\Section::make('Discord')->schema([
                    Forms\Components\Toggle::make('ticket_notifications')
                        ->hintIcon('heroicon-o-chat-bubble-left-right')
                        ->helperText('Receive Discord notifications when your ticket is updated')
                        ->label('Ticket notifications'),
                ]),
            ]);
    }
}
