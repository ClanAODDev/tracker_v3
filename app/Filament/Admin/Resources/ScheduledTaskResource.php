<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\ScheduledTaskResource\Pages\ListScheduledTasks;
use App\Filament\Admin\Resources\ScheduledTaskResource\Pages\ViewScheduledTask;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Contracts\Console\Kernel as ConsoleKernel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Lorisleiva\CronTranslator\CronTranslator;
use OptimatesDE\FilamentScheduleMonitor\FilamentScheduleMonitorPlugin;
use OptimatesDE\FilamentScheduleMonitor\Resources\MonitoredScheduledTaskResource;
use OptimatesDE\FilamentScheduleMonitor\Resources\MonitoredScheduledTaskResource\RelationManagers\LogItemsRelationManager;
use Spatie\ScheduleMonitor\Models\MonitoredScheduledTask;
use Throwable;

class ScheduledTaskResource extends MonitoredScheduledTaskResource
{
    protected static function plugin(): ?FilamentScheduleMonitorPlugin
    {
        return null;
    }

    public static function getNavigationGroup(): string|\UnitEnum|null
    {
        return 'System';
    }

    public static function getNavigationSort(): ?int
    {
        return 3;
    }

    public static function canUpdate(Model $record): bool
    {
        return true;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('name')
            ->columns([
                ToggleColumn::make('is_enabled')
                    ->label('Enabled'),
                TextColumn::make('name')
                    ->label(static::trans('columns.name'))
                    ->description(fn (MonitoredScheduledTask $record): ?string => static::descriptionFor($record->name))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('cron_expression')
                    ->label('Frequency')
                    ->state(fn (MonitoredScheduledTask $record): string => static::humanFrequency($record->cron_expression))
                    ->badge()
                    ->color('gray'),
                TextColumn::make('status')
                    ->label(static::trans('columns.status'))
                    ->badge()
                    ->state(fn (MonitoredScheduledTask $record): string => static::statusKey($record))
                    ->formatStateUsing(fn (string $state): string => static::trans("status.{$state}"))
                    ->color(fn (string $state): string => match ($state) {
                        'failed' => 'danger',
                        'ok'     => 'success',
                        default  => 'gray',
                    }),
                TextColumn::make('last_finished_at')
                    ->label(static::trans('columns.last_finished'))
                    ->dateTime('d.m.Y H:i')
                    ->placeholder('—')
                    ->sortable(),
                TextColumn::make('last_failed_at')
                    ->label(static::trans('columns.last_failed'))
                    ->dateTime('d.m.Y H:i')
                    ->placeholder('—')
                    ->sortable(),
            ])
            ->recordUrl(fn (MonitoredScheduledTask $record): string => static::getUrl('view', ['record' => $record]))
            ->emptyStateHeading(static::trans('empty.list_heading'))
            ->emptyStateDescription(static::trans('empty.list_description'))
            ->emptyStateIcon('heroicon-o-clock');
    }

    public static function getRelations(): array
    {
        return [
            LogItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListScheduledTasks::route('/'),
            'view'  => ViewScheduledTask::route('/{record}'),
        ];
    }

    protected static function descriptionFor(string $name): ?string
    {
        return static::scheduleDescriptions()[$name] ?? null;
    }

    protected static function scheduleDescriptions(): array
    {
        static $map = null;

        if ($map !== null) {
            return $map;
        }

        $map    = [];
        $events = app(Schedule::class)->events();

        if ($events === []) {
            try {
                app(ConsoleKernel::class)->bootstrap();
                $events = app(Schedule::class)->events();
            } catch (Throwable) {
                $events = [];
            }
        }

        foreach ($events as $event) {
            $description = $event->description;

            if (blank($description)) {
                continue;
            }

            $name = $event->monitorName
                ?? (str_contains((string) $event->command, 'artisan')
                    ? ltrim(Str::after((string) $event->command, 'artisan'), " '\"")
                    : ($event->getSummaryForDisplay() ?: null));

            if (filled($name)) {
                $map[rtrim($name, "'\"")] = $description;
            }
        }

        return $map;
    }

    protected static function humanFrequency(string $cron): string
    {
        try {
            return CronTranslator::translate($cron);
        } catch (Throwable) {
            return $cron;
        }
    }
}
