<?php

namespace App\Filament\Mod\Resources;

use App\Filament\Mod\Resources\DivisionResource\Pages;
use App\Models\Division;
use Filament\Forms;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Mansoor\FilamentVersionable\Table\RevisionsAction;

class DivisionResource extends Resource
{
    protected static ?string $model = Division::class;

    protected static ?string $navigationIcon = 'heroicon-s-cog';

    protected static ?string $label = 'Settings';

    protected static ?string $navigationGroup = 'Division';

    public static function form(Form $form): Form
    {
        $channelOptions = [
            'officers' => 'Officer Channel',
            'members' => 'Members Channel',
            false => 'Disabled',
        ];

        return $form
            ->schema([
                Forms\Components\Section::make('General')
                    ->description('Basic division settings')
                    ->aside()
                    ->schema([
                        Forms\Components\TextInput::make('name')->helperText('Consult admin to update')->readOnly(),
                        Forms\Components\TextInput::make('description')->helperText('Sub-header division text *DEPRECATING*'),
                        Forms\Components\TextInput::make('division structure')
                            ->helperText('Numerical id of your division\'s division structure thread *DEPRECATING*')
                            ->numeric()
                            ->statePath('settings.division_structure'),
                        Forms\Components\TextInput::make('welcome area')
                            ->helperText('Numerical id of your division\'s welcome area.')
                            ->numeric()
                            ->statePath('settings.welcome_area'),
                        Forms\Components\TextInput::make('inactivity days')
                            ->helperText('Number of days without VoIP activity before a member is considered inactive.')
                            ->numeric()
                            ->statePath('settings.inactivity_days'),
                    ])->columns(),

                Forms\Components\Section::make('Locality')->collapsible()->collapsed()
                    ->description('Update common vernacular to match division needs')
                    ->aside()
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

                Forms\Components\Section::make('Recruiting')->collapsible()->collapsed()
                    ->description('Settings related to division recruitment process')
                    ->aside()
                    ->statePath('settings')->schema([
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

                Forms\Components\Section::make('Chat Notifications')->collapsible()->collapsed()
                    ->description('Specify which events should notify and where.')
                    ->aside()
                    ->columns()
                    ->statePath('settings.chat_alerts')
                    ->schema([
                        Fieldset::make('Recruitment')
                            ->schema([
                                Forms\Components\Select::make('member_created')
                                    ->options($channelOptions)
                                    ->label('New Recruitments'),
                                Forms\Components\Select::make('member_approved')
                                    ->options($channelOptions)
                                    ->label('New Recruit Approval'),
                                Forms\Components\Select::make('member_denied')
                                    ->options($channelOptions)
                                    ->label('New Recruit Denial'),
                            ])
                            ->columns(3),

                        Fieldset::make('Membership Changes')
                            ->schema([
                                Forms\Components\Select::make('member_removed')
                                    ->options($channelOptions)
                                    ->label('Member Removals'),
                                Forms\Components\Select::make('member_transferred')
                                    ->options($channelOptions)
                                    ->label('Member Transfer'),
                                Forms\Components\Select::make('pt_member_removed')
                                    ->options($channelOptions)
                                    ->label('Part-Time Member Removal'),
                            ])
                            ->columns(3),

                        Fieldset::make('Administrative Updates')
                            ->schema([
                                Forms\Components\Select::make('division_edited')
                                    ->options($channelOptions)
                                    ->label('Division Settings Changes'),
                                Forms\Components\Select::make('rank_changed')
                                    ->options($channelOptions)
                                    ->label('Member Rank Changes'),
                                Forms\Components\Select::make('member_awarded')
                                    ->options($channelOptions)
                                    ->label('Member Awarded'),
                            ])
                            ->columns(3),
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
                RevisionsAction::make(),
            ])
            ->bulkActions([
                //
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
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
