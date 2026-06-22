<?php

namespace App\Filament\Admin\Resources;

use Filament\Support\Enums\FontFamily;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use OptimatesDE\FilamentScheduleMonitor\Resources\MonitoredScheduledTaskResource;
use Spatie\ScheduleMonitor\Models\MonitoredScheduledTask;

class ScheduledTaskResource extends MonitoredScheduledTaskResource
{
    public static function getNavigationGroup(): string|\UnitEnum|null
    {
        return 'Admin';
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
                    ->label(static::trans('columns.cron'))
                    ->fontFamily(FontFamily::Mono)
                    ->badge()
                    ->color('gray'),
                TextColumn::make('status')
                    ->label(static::trans('columns.status'))
                    ->badge()
                    ->state(fn (MonitoredScheduledTask $record): string => static::statusKey($record))
                    ->formatStateUsing(fn (string $state): string => static::trans("status.{$state}"))
                    ->color(fn (string $state): string => match ($state) {
                        'failed' => 'danger',
                        'ok' => 'success',
                        default => 'gray',
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
}
