<?php

namespace App\Filament\Actions\Members;

use App\Models\Member;
use Filament\Actions\Action;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Utilities\Get;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\HtmlString;

class CleanupUnassignedLeadersAction extends Action
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Unassigned leaders')
            ->requiresConfirmation()
            ->schema([

                Placeholder::make('preview')
                    ->label('Preview Change')
                    ->content(function (Get $get): HtmlString {
                        $rule = $get('rule');

                        $c2 = in_array($rule, ['squad_pos2', 'both'], true)
                            ? Member::unassignedSquadLeaders()->count()
                            : 0;

                        $c3 = in_array($rule, ['platoon_pos3', 'both'], true)
                            ? Member::unassignedPlatoonLeaders()->count()
                            : 0;

                        if (max($c2, $c3) === 0 && $rule !== null) {
                            return new HtmlString('No members will be affected.');
                        }

                        $html = match ($rule) {
                            'squad_pos2' => "Will update <b>{$c2}</b> unassigned squad leaders",
                            'platoon_pos3' => "Will update <b>{$c3}</b> unassigned platoon leaders.",
                            'both' => "Will update: <br /><b>{$c2}</b> unassigned squad leaders <br /><b>{$c3}</b> unassigned platoon leaders.",
                            default => '<span class="text-gray-500">Select a rule to see how many members will be affected.</span>',
                        };

                        return new HtmlString($html);
                    }),

                Select::make('rule')
                    ->label('Which rule to run?')
                    ->options([
                        'squad_pos2' => 'Unassigned squad leaders',
                        'platoon_pos3' => 'Unassigned platoon leaders',
                        'both' => 'Run both rules',
                    ])
                    ->required()
                    ->reactive()
                    ->native(false),
            ])
            ->modalHeading('Confirm maintenance action')
            ->modalDescription('Unassigned leader cleanup')
            ->action(function (array $data): void {
                $rule = $data['rule'] ?? 'squad_pos2';

                $updated2 = 0;
                $updated3 = 0;

                DB::transaction(function () use ($rule, &$updated2, &$updated3) {
                    if ($rule === 'squad_pos2' || $rule === 'both') {
                        $updated2 = Member::unassignedSquadLeaders()->update(['position' => 1]);
                    }

                    if ($rule === 'platoon_pos3' || $rule === 'both') {
                        $updated3 = Member::unassignedPlatoonLeaders()->update(['position' => 1]);
                    }
                });

                $summary = match ($rule) {
                    'squad_pos2' => "Updated {$updated2} member(s) from position 2 → 1.",
                    'platoon_pos3' => "Updated {$updated3} member(s) from position 3 → 1.",
                    'both' => "Updated {$updated2} member(s) (pos=2 → 1) and {$updated3} member(s) (pos=3 → 1).",
                    default => 'No changes performed.',
                };

                Notification::make()
                    ->title('Maintenance action completed')
                    ->body($summary)
                    ->persistent()
                    ->success()
                    ->send();
            });
    }

    /**
     * Compute current counts for the live modal preview.
     *
     * @return array{int,int} [pos2NonSquadLeaders, pos3NonPlatoonLeaders]
     */
    protected function counts(): array
    {
        $c2 = Member::unassignedSquadLeaders()->count();
        $c3 = Member::unassignedPlatoonLeaders()->count();

        return [$c2, $c3];
    }

    public static function getDefaultName(): ?string
    {
        return 'combinedDemotions';
    }
}
