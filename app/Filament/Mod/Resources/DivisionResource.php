<?php

namespace App\Filament\Mod\Resources;

use App\Enums\Rank;
use App\Filament\Mod\Resources\DivisionResource\Pages;
use App\Filament\Mod\Resources\DivisionResource\RelationManagers\PlatoonsRelationManager;
use App\Filament\Mod\Resources\DivisionResource\RelationManagers\TagsRelationManager;
use App\Models\Division;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
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
                                ]),
                            ]),
                    ]),

                Forms\Components\Section::make('Recruiting')
                    ->description('Settings related to division recruitment process')
                    ->aside()
                    ->statePath('settings')->schema([

                        Forms\Components\TextInput::make('Applications Feed')
                            ->label('Recruit Applications RSS URL')
                            ->statePath('recruitment_rss_feed')
                            ->url()
                            ->rules([
                                fn (): \Closure => function (string $attribute, $value, \Closure $fail) {
                                    if (empty($value)) {
                                        return;
                                    }

                                    try {
                                        $response = \Illuminate\Support\Facades\Http::withUserAgent('Tracker - RSS Validator')
                                            ->timeout(10)
                                            ->get($value);

                                        if (! $response->ok()) {
                                            $fail('The URL returned an error (HTTP ' . $response->status() . ')');

                                            return;
                                        }

                                        $content = $response->body();
                                        $xml = @simplexml_load_string($content);

                                        if ($xml === false) {
                                            $fail('The URL does not return valid XML');

                                            return;
                                        }

                                        if (! isset($xml->channel) && ! isset($xml->entry)) {
                                            $fail('The URL does not appear to be a valid RSS or Atom feed');
                                        }
                                    } catch (\Exception $e) {
                                        $fail('Could not fetch URL: ' . $e->getMessage());
                                    }
                                },
                            ])
                            ->helperText('RSS feed URL where new division applications are posted'),

                        Forms\Components\Section::make('Tasks')->collapsible()->collapsed()
                            ->description('Critical steps to perform during recruitment')
                            ->schema([
                                Forms\Components\Repeater::make('recruiting_tasks')->schema([
                                    Forms\Components\Textarea::make('task_description')->hiddenLabel(),
                                ]),
                            ]),
                        Forms\Components\Section::make('Informational threads')->collapsible()->collapsed()
                            ->description('Important forum threads for new recruits to be aware of')
                            ->schema([
                                Forms\Components\Repeater::make('recruiting_threads')->schema([
                                    Forms\Components\TextInput::make('thread_name')->columnSpanFull(),
                                    Forms\Components\TextInput::make('thread_url')
                                        ->label('Thread URL')
                                        ->url()
                                        ->columnSpanFull(),
                                    Forms\Components\Textarea::make('comments')->columnSpanFull(),
                                ]),
                            ]),
                        Forms\Components\Textarea::make('welcome dm')
                            ->rows(6)
                            ->columnSpanFull()
                            ->helperText('Available replacement tags (wrap with {{ tag }}): ingame_name, name')
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
                                        Select::make('member_applied')
                                            ->options($channelOptions)
                                            ->default(false)
                                            ->label('New Applications'),
                                        Select::make('member_created')
                                            ->options($channelOptions)
                                            ->default(false)
                                            ->label('New Recruitments'),
                                        Select::make('member_approved')
                                            ->options($channelOptions)
                                            ->default(false)
                                            ->label('New Recruit Approval'),
                                    ])
                                    ->columns(3),

                                Tab::make('Membership Changes')
                                    ->schema([
                                        Select::make('member_removed')
                                            ->options($channelOptions)
                                            ->default(false)
                                            ->label('Member Removals'),
                                        Select::make('member_transferred')
                                            ->options($channelOptions)
                                            ->default(false)
                                            ->label('Member Transfer'),
                                        Select::make('pt_member_removed')
                                            ->options($channelOptions)
                                            ->default(false)
                                            ->label('Part‑Time Member Removal'),
                                    ])
                                    ->columns(3),

                                Tab::make('Administrative Updates')
                                    ->schema([
                                        Select::make('division_edited')
                                            ->options($channelOptions)
                                            ->default(false)
                                            ->label('Division Settings Changes'),
                                        Select::make('member_promoted')
                                            ->options($channelOptions)
                                            ->default(false)
                                            ->label('Member Promoted'),
                                        Select::make('member_awarded')
                                            ->options($channelOptions)
                                            ->default(false)
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
                            ])
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('meta_description')
                            ->maxLength(100)
                            ->statePath('settings.meta_description')
                            ->helperText('60-100 character summary of division for SEO purposes. Exposed in URL previews / unfurling.'),
                        Forms\Components\Section::make('Screenshot Gallery')
                            ->description('Upload screenshots to display on your division page')
                            ->collapsible()
                            ->collapsed()
                            ->schema([
                                Forms\Components\FileUpload::make('screenshots')
                                    ->label('Screenshots')
                                    ->multiple()
                                    ->disk('public')
                                    ->directory('division-screenshots')
                                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/gif', 'image/webp'])
                                    ->maxSize(5120)
                                    ->maxFiles(20)
                                    ->reorderable()
                                    ->appendFiles()
                                    ->helperText('Drag and drop to reorder. Max 20 images, 5MB each.'),
                            ]),
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
            TagsRelationManager::class,
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
