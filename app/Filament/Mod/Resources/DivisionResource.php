<?php

namespace App\Filament\Mod\Resources;

use App\Enums\Position;
use App\Enums\Rank;
use App\Filament\Mod\Resources\DivisionResource\Pages;
use App\Filament\Mod\Resources\DivisionResource\RelationManagers\PlatoonsRelationManager;
use App\Models\Division;
use App\Models\Member;
use Filament\Forms;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class DivisionResource extends Resource
{
    protected static ?string $model = Division::class;

    protected static ?string $navigationIcon = 'heroicon-s-cog';

    protected static ?string $label = 'Settings';

    protected static ?string $navigationGroup = 'Division';

    public static function canViewAny(): bool
    {
        return auth()->user()->isRole(['admin']) || auth()->user()->isDivisionLeader();
    }

    public static function form(Form $form): Form
    {
        $channelOptions = [
            'officers' => 'Officers',
            'members' => 'Members',
            false => 'Disabled',
        ];

        return $form
            ->schema([

                Forms\Components\Section::make('General')
                    ->description('Basic division settings')
                    ->aside()
                    ->schema([
                        Tabs::make('Settings')
                            ->tabs([
                                Tabs\Tab::make('General')
                                    ->schema([
                                        TextInput::make('name')
                                            ->label('Division Name')
                                            ->helperText('Consult admin to update')
                                            ->readOnly(),
                                        TextInput::make('description')
                                            ->label('Sub‑header Text')
                                            ->helperText('Division sub‑header (*DEPRECATING*)'),
                                    ])
                                    ->columns(2),

                                Tabs\Tab::make('Welcome Area')
                                    ->schema([
                                        TextInput::make('settings.welcome_area')
                                            ->label('Welcome Area ID')
                                            ->numeric()
                                            ->helperText('Division welcome area ID'),

                                        Forms\Components\Toggle::make('settings.use_welcome_thread')
                                            ->label('Use Welcome Thread')
                                            ->helperText('Recruit welcome area is a thread instead of a forum'),
                                    ])
                                    ->columns(),

                                Tabs\Tab::make('Behavior')
                                    ->schema([
                                        TextInput::make('settings.inactivity_days')
                                            ->label('Inactivity Threshold')
                                            ->numeric()
                                            ->helperText('Days without VoIP before marking inactive'),
                                    ]),

                                Tabs\Tab::make('Promotion')
                                    ->schema([
                                        Select::make('settings.max_platoon_leader_rank')
                                            ->label('PL Promotion Cap')
                                            ->options([
                                                Rank::CADET->value => Rank::CADET->getLabel(),
                                                Rank::PRIVATE->value => Rank::PRIVATE->getLabel(),
                                                Rank::PRIVATE_FIRST_CLASS->value => Rank::PRIVATE_FIRST_CLASS->getLabel(),
                                            ])
                                            ->helperText('Highest rank PLs can promote to without approval'),
                                    ]),

                                Tab::make('Locality')->schema([
                                    Forms\Components\Section::make()
                                        ->description('Update common vernacular to match division needs')
                                        ->statePath('settings')->schema([
                                            Forms\Components\Repeater::make('locality')->schema([
                                                Forms\Components\TextInput::make('old-string')
                                                    ->label('Replace')
                                                    ->readOnly(),
                                                Forms\Components\TextInput::make('new-string')
                                                    ->required()
                                                    ->label('With'),
                                            ])->reorderable(false)->columns()
                                                ->addable(false)
                                                ->deletable(false),
                                        ]),
                                ])
                            ]),
                    ]),

                Forms\Components\Section::make('Leadership Management')
                    ->description('Manage division leaders')
                    ->aside()
                    ->schema([
                        Forms\Components\Section::make()->schema([
                            TextInput::make('current_co_name')
                                ->label('Current CO')
                                ->disabled()
                                ->dehydrated(false)
                                ->afterStateHydrated(function ($state, Set $set) use ($form) {
                                    $coName = \App\Models\Member::query()
                                        ->where('division_id', $form->getRecord()->id)
                                        ->where('position', \App\Enums\Position::COMMANDING_OFFICER)
                                        ->value('name');
                                    $set('current_co_name', $coName);
                                }),

                            Select::make('new_co')
                                ->label('New CO')
                                ->options(fn () => Member::where('division_id', $form->getRecord()->id)
                                    ->pluck('name', 'id')),
                        ])->columns(),

                        Repeater::make('executive_officers')
                            ->label('Executive Officers')
                            ->schema([
                                Select::make('xo')
                                    ->label('Executive Officer')
                                    ->options(fn () => Member::where('division_id', $form->getRecord()->id)
                                        ->pluck('name', 'id'))
                                    ->searchable()
                                    ->required(),
                            ])
                            ->minItems(0)
                            ->maxItems(5)
                            ->addActionLabel('Add XO')
                            ->afterStateHydrated(function ($state, Set $set) use ($form) {
                                if (! empty($state)) {
                                    return;
                                }

                                $rows = Member::where('division_id', $form->getRecord()->id)
                                    ->where('position', Position::EXECUTIVE_OFFICER)
                                    ->pluck('id')
                                    ->map(fn ($id) => ['xo' => $id])
                                    ->toArray();

                                $set('executive_officers', $rows);
                            })
                            ->required(),

                    ]),

                Forms\Components\Section::make('Recruiting')
                    ->description('Settings related to division recruitment process')
                    ->aside()
                    ->statePath('settings')->schema([

                        Forms\Components\TextInput::make('Applications Feed')
                            ->label('Recruit Applications RSS URL')
                            ->statePath('recruitment_rss_feed')
                            ->required()
                            ->helperText('RSS feed URL where new division applications are posted'),

                        Forms\Components\Section::make('Tasks')->collapsible()->collapsed()
                            ->description('Critical steps to perform during recruitment')
                            ->schema([
                                Forms\Components\Repeater::make('recruiting_tasks')->schema([
                                    Forms\Components\Textarea::make('task_description'),
                                ]),
                            ]),
                        Forms\Components\Section::make('Informational threads')->collapsible()->collapsed()
                            ->description('Important forum threads for new recruits to be aware of')
                            ->schema([
                                Forms\Components\Repeater::make('recruiting_threads')->schema([
                                    Forms\Components\TextInput::make('thread_name'),
                                    Forms\Components\TextInput::make('thread_id'),
                                    Forms\Components\Textarea::make('comments')->columnSpanFull(),
                                ])->columns(),
                            ]),
                        Forms\Components\Textarea::make('welcome pm')
                            ->rows(6)
                            ->columnSpanFull()
                            ->helperText('Use {{ name }} to insert the new recruit\'s name into your message')
                            ->statePath('welcome_pm'),
                    ]),

                Forms\Components\Section::make('Chat Notifications')
                    ->description('Specify which events should notify and where.')
                    ->aside()
                    ->statePath('settings.chat_alerts')
                    ->schema([
                        Forms\Components\Tabs::make('Chat Notifications')
                            ->tabs([
                                Tab::make('Recruitment')
                                    ->schema([
                                        Select::make('member_created')
                                            ->options($channelOptions)
                                            ->label('New Recruitments'),
                                        Select::make('member_approved')
                                            ->options($channelOptions)
                                            ->label('New Recruit Approval'),
                                    ])
                                    ->columns(2),

                                Tab::make('Membership Changes')
                                    ->schema([
                                        Select::make('member_removed')
                                            ->options($channelOptions)
                                            ->label('Member Removals'),
                                        Select::make('member_transferred')
                                            ->options($channelOptions)
                                            ->label('Member Transfer'),
                                        Select::make('pt_member_removed')
                                            ->options($channelOptions)
                                            ->label('Part‑Time Member Removal'),
                                    ])
                                    ->columns(3),

                                Tab::make('Administrative Updates')
                                    ->schema([
                                        Select::make('division_edited')
                                            ->options($channelOptions)
                                            ->label('Division Settings Changes'),
                                        Select::make('member_promoted')
                                            ->options($channelOptions)
                                            ->label('Member Promoted'),
                                        Select::make('member_awarded')
                                            ->options($channelOptions)
                                            ->label('Member Awarded'),
                                    ])
                                    ->columns(3),
                            ]),
                    ]),
                Forms\Components\Section::make('Website')
                    ->description('Divisional website settings')
                    ->aside()
                    ->schema([
                        Forms\Components\MarkdownEditor::make('site_content')
                            ->helperText('Changes will prompt an admin review before being published')
                            ->toolbarButtons([
                                'blockquote',
                                'bold',
                                'bulletList',
                                'codeBlock',
                                'heading',
                                'italic',
                                'link',
                                'orderedList',
                                'redo',
                                'strike',
                                'undo',
                                // 'table',
                                // 'attachFiles',
                            ])
                            ->columnSpanFull(),
                    ]),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->sortable(),
            ])
            ->filters([
                Tables\Filters\Filter::make('active')
                    ->query(fn (Builder $query): Builder => $query->where('active', true))->default(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                //
            ]);
    }

    public static function getRelations(): array
    {
        return [
            PlatoonsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'revisions' => Pages\DivisionRevisions::route('/{record}/revisions'),
            'index' => Pages\ListDivisions::route('/'),
            'edit' => Pages\EditDivision::route('/{record}/edit'),
        ];
    }
}
