<?php

namespace App\Filament\Mod\Resources\ActivityResource\Pages;

use App\Enums\ActivityType;
use App\Filament\Mod\Resources\ActivityResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListActivities extends ListRecords
{
    protected static string $resource = ActivityResource::class;

    public function getTabs(): array
    {
        $baseQuery = fn () => ActivityResource::getEloquentQuery();

        return [
            'all' => Tab::make('All')
                ->badge($baseQuery()->count()),
            'recruited' => Tab::make('Recruits')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('name', ActivityType::RECRUITED))
                ->badge($baseQuery()->where('name', ActivityType::RECRUITED)->count())
                ->badgeColor('success'),
            'removed' => Tab::make('Removals')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('name', ActivityType::REMOVED))
                ->badge($baseQuery()->where('name', ActivityType::REMOVED)->count())
                ->badgeColor('danger'),
            'transferred' => Tab::make('Transferred')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('name', ActivityType::TRANSFERRED))
                ->badge($baseQuery()->where('name', ActivityType::TRANSFERRED)->count())
                ->badgeColor('primary'),
            'flagged' => Tab::make('Flagged')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereIn('name', [ActivityType::FLAGGED, ActivityType::UNFLAGGED]))
                ->badge($baseQuery()->whereIn('name', [ActivityType::FLAGGED, ActivityType::UNFLAGGED])->count())
                ->badgeColor('warning'),
        ];
    }
}
