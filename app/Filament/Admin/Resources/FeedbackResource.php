<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\FeedbackResource\Pages\ListFeedback;
use App\Filament\Admin\Resources\FeedbackResource\Pages\ViewFeedback;
use App\Models\Feedback;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\TextSize;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class FeedbackResource extends Resource
{
    protected static ?string $model = Feedback::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected static string|\UnitEnum|null $navigationGroup = 'System';

    protected static ?int $navigationSort = 1;

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->columns(2)
                    ->schema([
                        TextEntry::make('user.name')
                            ->label('Submitted by')
                            ->icon('heroicon-m-user'),
                        TextEntry::make('created_at')
                            ->label('Submitted at')
                            ->icon('heroicon-m-clock')
                            ->dateTime(),
                        TextEntry::make('url')
                            ->label('Page URL')
                            ->icon('heroicon-m-link')
                            ->url(fn ($record) => $record->url)
                            ->openUrlInNewTab()
                            ->columnSpanFull()
                            ->hidden(fn ($record) => ! $record->url),
                    ]),

                Section::make('Feedback')
                    ->schema([
                        TextEntry::make('body')
                            ->label('')
                            ->size(TextSize::Large)
                            ->columnSpanFull(),
                    ]),

                Section::make('Screenshots')
                    ->hidden(fn ($record) => empty($record->screenshots))
                    ->schema([
                        ImageEntry::make('screenshots')
                            ->label('')
                            ->height(80)
                            ->columnSpanFull()
                            ->extraImgAttributes([
                                'style'        => 'cursor:zoom-in;border-radius:6px;',
                                'data-preview' => 'true',
                                'onclick'      => "window.dispatchEvent(new CustomEvent('open-image-preview',{detail:{src:this.src}}))",
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('user.name')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('body')
                    ->limit(80)
                    ->wrap(),
                ImageColumn::make('screenshots')
                    ->height(48)
                    ->stacked()
                    ->hidden(fn (?Feedback $record) => empty($record?->screenshots)),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->recordActions([
                ViewAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListFeedback::route('/'),
            'view'  => ViewFeedback::route('/{record}'),
        ];
    }
}
