<?php

namespace App\Filament\Mod\Resources;

use App\Filament\Mod\Resources\DivisionResource\Pages;
use App\Models\Division;
use Filament\Forms;
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
                Forms\Components\Section::make('Locality')->collapsible()->collapsed()
                    ->description('Update common vernacular to match division needs')
                    ->statePath('settings')->schema([
                        Forms\Components\Repeater::make('locality')->schema([
                            Forms\Components\TextInput::make('old-string')->readOnly(),
                            Forms\Components\TextInput::make('new-string'),
                        ])->reorderable(false)->columns()
                            ->addable(false)
                            ->deletable(false),
                    ]),

                Forms\Components\Section::make('Recruiting')->collapsible()->collapsed()
                    ->description('Settings related to division recruitment process')
                    ->statePath('settings')->schema([
                        Forms\Components\Section::make('Tasks')->collapsible()->collapsed()
                            ->description('Critical steps to perform during recruitment')
                            ->schema([
                                Forms\Components\Repeater::make('recruiting_tasks')->schema([
                                    Forms\Components\TextInput::make('task_description'),
                                ]),
                            ]),
                        Forms\Components\Section::make('Informational threads')->collapsible()->collapsed()
                            ->description('Important forum threads for new recruits to be aware of')
                            ->schema([
                                Forms\Components\Repeater::make('recruiting_threads')->schema([
                                    Forms\Components\TextInput::make('thread_name'),
                                    Forms\Components\TextInput::make('thread_id'),
                                    Forms\Components\TextInput::make('comments')->columnSpanFull(),
                                ])->columns(),
                            ]),
                        Forms\Components\Textarea::make('welcome pm')
                            ->rows(6)
                            ->columnSpanFull()
                            ->helperText('Use {{ name }} to insert the new recruit\'s name into your message')
                            ->statePath('welcome_pm'),
                    ]),
                Forms\Components\Section::make('Officer Notifications')->collapsible()->collapsed()
                    ->description('Specify which events should notify and where.')
                    ->columns()
                    ->statePath('settings')
                    ->schema([
                        Forms\Components\Select::make('voice_alert_created_member')->options($channelOptions)
                            ->label('New Recruitments')
                            ->selectablePlaceholder(false),
                        Forms\Components\Select::make('voice_alert_removed_member')->options($channelOptions)
                            ->label('Member Removals')
                            ->selectablePlaceholder(false),
                        Forms\Components\Select::make('voice_alert_division_edited')->options($channelOptions)
                            ->label('Division settings changes')
                            ->selectablePlaceholder(false),
                        Forms\Components\Select::make('voice_alert_member_approved')->options($channelOptions)
                            ->label('New Recruit Approval')
                            ->selectablePlaceholder(false),
                        Forms\Components\Select::make('voice_alert_member_denied')->options($channelOptions)
                            ->label('New Recruit Denial')
                            ->selectablePlaceholder(false),
                        Forms\Components\Select::make('voice_alert_pt_member_removed')->options($channelOptions)
                            ->label('Part-Time member removal')
                            ->selectablePlaceholder(false),
                        Forms\Components\Select::make('voice_alert_member_transferred')->options($channelOptions)
                            ->label('Member Transfer')
                            ->selectablePlaceholder(false),
                        Forms\Components\Select::make('voice_alert_rank_changed')->options($channelOptions)
                            ->label('Member Rank Changes')
                            ->selectablePlaceholder(false),
                    ]),
                Forms\Components\Section::make('Website')
                    ->description('Divisional website settings')
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
                    ])->collapsible()->collapsed(),

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
