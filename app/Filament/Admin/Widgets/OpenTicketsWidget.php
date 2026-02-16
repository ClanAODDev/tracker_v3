<?php

namespace App\Filament\Admin\Widgets;

use App\Filament\Admin\Resources\TicketResource;
use App\Models\Ticket;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class OpenTicketsWidget extends BaseWidget
{
    protected static ?int $sort = 0;

    protected function getStats(): array
    {
        $unassigned = Ticket::whereNull('owner_id')
            ->whereNotIn('state', ['resolved', 'rejected'])
            ->count();

        $inProgress = Ticket::whereNotNull('owner_id')
            ->whereNotIn('state', ['resolved', 'rejected'])
            ->count();

        $myAssigned = Ticket::where('owner_id', auth()->id())
            ->whereNotIn('state', ['resolved', 'rejected'])
            ->count();

        return [
            Stat::make('Unassigned Tickets', $unassigned)
                ->description('Awaiting assignment')
                ->descriptionIcon('heroicon-m-inbox')
                ->color($unassigned > 0 ? 'danger' : 'success')
                ->url(TicketResource::getUrl('index') . '?' . http_build_query([
                    'filters' => [
                        'assignment' => ['value' => 'unassigned'],
                    ],
                ])),

            Stat::make('In Progress', $inProgress)
                ->description('Assigned but unresolved')
                ->descriptionIcon('heroicon-m-clock')
                ->color($inProgress > 0 ? 'warning' : 'success')
                ->url(TicketResource::getUrl('index') . '?' . http_build_query([
                    'filters' => [
                        'state' => ['values' => ['new', 'assigned']],
                    ],
                ])),

            Stat::make('My Tickets', $myAssigned)
                ->description('Assigned to you')
                ->descriptionIcon('heroicon-m-user')
                ->color($myAssigned > 0 ? 'info' : 'gray')
                ->url(TicketResource::getUrl('index') . '?' . http_build_query([
                    'filters' => [
                        'state'      => ['values' => ['assigned']],
                        'assignment' => ['value' => 'mine'],
                    ],
                ])),
        ];
    }
}
