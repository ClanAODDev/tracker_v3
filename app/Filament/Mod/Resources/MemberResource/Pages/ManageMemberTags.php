<?php

namespace App\Filament\Mod\Resources\MemberResource\Pages;

use App\Filament\Mod\Resources\MemberResource;
use App\Models\DivisionTag;
use App\Models\Member;
use App\Models\Platoon;
use App\Models\Squad;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\HtmlString;

class ManageMemberTags extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string $resource = MemberResource::class;

    protected static string $view = 'filament.mod.resources.member-resource.pages.manage-member-tags';

    protected static ?string $title = 'Member Tags';

    protected static ?string $navigationIcon = 'heroicon-o-tag';

    public static function canAccess(array $parameters = []): bool
    {
        return auth()->user()->can('assign', DivisionTag::class);
    }

    public function table(Table $table): Table
    {
        $user = auth()->user();
        $divisionId = $user->member?->division_id;

        return $table
            ->query($this->getTableQuery())
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('rank')
                    ->badge()
                    ->sortable(),
                TextColumn::make('platoon.name')
                    ->label('Platoon')
                    ->sortable(),
                TextColumn::make('tags')
                    ->label('Tags')
                    ->formatStateUsing(function ($state, Member $record) {
                        $tags = $record->tags;
                        if ($tags->isEmpty()) {
                            return new HtmlString('<span class="text-gray-400">No tags</span>');
                        }

                        return new HtmlString(
                            $tags->map(function ($tag) {
                                $bgColor = $tag->color ?? '#6b7280';
                                $textColor = $this->getContrastColor($bgColor);

                                return sprintf(
                                    '<span class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-full mr-1" style="background-color: %s; color: %s">%s</span>',
                                    $bgColor,
                                    $textColor,
                                    e($tag->name)
                                );
                            })->join('')
                        );
                    })
                    ->html(),
            ])
            ->filters([
                SelectFilter::make('platoon_id')
                    ->label('Platoon')
                    ->options(fn () => Platoon::where('division_id', auth()->user()->member?->division_id)
                        ->orderBy('name')
                        ->pluck('name', 'id'))
                    ->searchable(),
                SelectFilter::make('squad_id')
                    ->label('Squad')
                    ->options(function () {
                        $divisionId = auth()->user()->member?->division_id;

                        return Squad::whereHas('platoon', fn ($q) => $q->where('division_id', $divisionId))
                            ->orderBy('name')
                            ->pluck('name', 'id');
                    })
                    ->searchable(),
                SelectFilter::make('tags')
                    ->label('Has Tag')
                    ->relationship('tags', 'name', fn (Builder $query) => $query->where('division_id', auth()->user()->member?->division_id))
                    ->searchable()
                    ->preload(),
            ])
            ->recordAction(null)
            ->recordUrl(null)
            ->bulkActions([
                BulkActionGroup::make([
                    BulkAction::make('assign_tags')
                        ->label('Assign Tags')
                        ->icon('heroicon-o-plus')
                        ->visible(fn () => auth()->user()->can('assign', DivisionTag::class))
                        ->form([
                            Select::make('tags')
                                ->label('Tags to Assign')
                                ->multiple()
                                ->options(fn () => DivisionTag::where('division_id', auth()->user()->member?->division_id)
                                    ->pluck('name', 'id'))
                                ->required(),
                        ])
                        ->action(function (Collection $records, array $data): void {
                            $assignerId = auth()->user()->member?->id;

                            foreach ($records as $member) {
                                $pivotData = [];
                                foreach ($data['tags'] as $tagId) {
                                    $pivotData[$tagId] = ['assigned_by' => $assignerId];
                                }
                                $member->tags()->syncWithoutDetaching($pivotData);
                            }

                            Notification::make()
                                ->title('Tags assigned successfully')
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),

                    BulkAction::make('remove_tags')
                        ->label('Remove Tags')
                        ->icon('heroicon-o-minus')
                        ->color('danger')
                        ->visible(fn () => auth()->user()->can('assign', DivisionTag::class))
                        ->form([
                            Select::make('tags')
                                ->label('Tags to Remove')
                                ->multiple()
                                ->options(fn () => DivisionTag::where('division_id', auth()->user()->member?->division_id)
                                    ->pluck('name', 'id'))
                                ->required(),
                        ])
                        ->action(function (Collection $records, array $data): void {
                            foreach ($records as $member) {
                                $member->tags()->detach($data['tags']);
                            }

                            Notification::make()
                                ->title('Tags removed successfully')
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                ]),
            ])
            ->defaultSort('name');
    }

    protected function getTableQuery(): Builder
    {
        $user = auth()->user();
        $member = $user->member;
        $divisionId = $member?->division_id;

        $query = Member::query()
            ->with(['tags', 'platoon'])
            ->where(function (Builder $q) use ($divisionId) {
                $q->where('division_id', $divisionId)
                    ->orWhereHas('partTimeDivisions', function (Builder $ptQuery) use ($divisionId) {
                        $ptQuery->where('division_id', $divisionId);
                    });
            });

        if ($user->isPlatoonLeader() && ! $user->isDivisionLeader() && ! $user->isRole('admin')) {
            $query->where('platoon_id', $member->platoon_id);
        }

        return $query;
    }

    protected function getContrastColor(string $hexColor): string
    {
        $hex = ltrim($hexColor, '#');

        if (strlen($hex) === 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }

        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));

        $luminance = (0.299 * $r + 0.587 * $g + 0.114 * $b) / 255;

        return $luminance > 0.5 ? '#000000' : '#ffffff';
    }
}
