<?php

namespace App\Filament\Admin\Pages;

use App\Models\Member;
use App\Services\ForumProcedureService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;

class DiscordMismatchReport extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-exclamation-triangle';

    protected static string|\UnitEnum|null $navigationGroup = 'Admin';

    protected static ?string $title = 'Discord Mismatches';

    protected static ?string $slug = 'discord-mismatch-report';

    protected string $view = 'filament.admin.pages.discord-mismatch-report';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Member::query()
                    ->join('users', 'users.member_id', '=', 'members.id')
                    ->whereNotNull('members.discord_id')
                    ->whereNotNull('users.discord_id')
                    ->whereColumn('members.discord_id', '!=', 'users.discord_id')
                    ->select('members.*', 'users.discord_id as user_discord_id')
            )
            ->columns([
                TextColumn::make('name')
                    ->label('Member')
                    ->searchable(),
                TextColumn::make('clan_id')
                    ->label('Clan ID')
                    ->sortable(),
                TextColumn::make('discord_id')
                    ->label('Member Discord ID')
                    ->copyable(),
                TextColumn::make('user_discord_id')
                    ->label('User Discord ID (Correct)')
                    ->copyable(),
            ])
            ->paginated([10, 25, 50]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('fixAll')
                ->label('Fix All Mismatches')
                ->icon('heroicon-o-wrench-screwdriver')
                ->color('danger')
                ->requiresConfirmation()
                ->modalDescription('This will overwrite all member discord_id values with the user discord_id and sync each to the forum.')
                ->action(function (): void {
                    $mismatches = Member::query()
                        ->join('users', 'users.member_id', '=', 'members.id')
                        ->whereNotNull('members.discord_id')
                        ->whereNotNull('users.discord_id')
                        ->whereColumn('members.discord_id', '!=', 'users.discord_id')
                        ->select('members.*', 'users.discord_id as user_discord_id', 'users.discord_username as user_discord_username')
                        ->get();

                    $procedureService = app(ForumProcedureService::class);
                    $fixed            = 0;

                    foreach ($mismatches as $member) {
                        $member->withoutRelations()->update([
                            'discord_id' => $member->user_discord_id,
                        ]);

                        if ($member->user_discord_id && $member->user_discord_username) {
                            $procedureService->setDiscordInfo(
                                userId: $member->clan_id,
                                discordId: $member->user_discord_id,
                                discordTag: $member->user_discord_username,
                            );
                        }

                        $fixed++;
                    }

                    Notification::make()
                        ->title("Fixed {$fixed} mismatched record(s).")
                        ->success()
                        ->send();
                }),
        ];
    }
}
