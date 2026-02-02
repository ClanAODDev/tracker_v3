<?php

namespace App\Filament\Mod\Resources;

use App\Enums\Rank;
use App\Filament\Mod\Resources\DivisionResource\Pages\EditDivision;
use App\Filament\Mod\Resources\DivisionResource\Pages\ListDivisions;
use App\Filament\Mod\Resources\DivisionResource\RelationManagers\PlatoonsRelationManager;
use App\Models\Division;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class DivisionResource extends Resource
{
    protected static ?string $model = Division::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-s-cog';

    protected static ?string $label = 'Settings';

    protected static string|\UnitEnum|null $navigationGroup = 'Division';

    public static function shouldRegisterNavigation(): bool
    {
        $user = auth()->user();

        if ($user?->isRole('admin')) {
            return true;
        }

        return $user?->member?->division_id !== null && $user->isRole('sr_ldr');
    }

    public static function getNavigationUrl(): string
    {
        $user = auth()->user();

        if ($user?->isRole('admin')) {
            return static::getUrl('index');
        }

        $division = $user?->member?->division;

        if ($division) {
            return static::getUrl('edit', ['record' => $division]);
        }

        return static::getUrl('index');
    }

    public static function form(Schema $schema): Schema
    {
        $channelOptions = [
            'officers' => 'Officers',
            'members' => 'Members',
            false => 'Disabled',
        ];

        return $schema
            ->components([
                Tabs::make('Division Settings')
                    ->persistTabInQueryString()
                    ->columnSpanFull()
                    ->tabs([
                        Tab::make('General')
                            ->icon('heroicon-o-cog-6-tooth')
                            ->schema([
                                Section::make('Division Info')
                                    ->columns(2)
                                    ->schema([
                                        TextInput::make('name')
                                            ->label('Division Name')
                                            ->helperText('Consult admin to update')
                                            ->readOnly(),
                                        TextInput::make('description')
                                            ->label('Sub‑header Text')
                                            ->helperText('Division sub‑header (*DEPRECATING*)'),
                                    ]),

                                Section::make('Welcome Area')
                                    ->columns(2)
                                    ->schema([
                                        TextInput::make('settings.welcome_area')
                                            ->label('Welcome Area ID')
                                            ->numeric()
                                            ->helperText('Division welcome area ID'),
                                        Toggle::make('settings.use_welcome_thread')
                                            ->label('Use Welcome Thread')
                                            ->helperText('Recruit welcome area is a thread instead of a forum'),
                                    ]),

                                Section::make('Behavior')
                                    ->columns(2)
                                    ->schema([
                                        TextInput::make('settings.inactivity_days')
                                            ->label('Inactivity Threshold')
                                            ->numeric()
                                            ->helperText('Days without VoIP before marking inactive'),
                                        Select::make('settings.max_platoon_leader_rank')
                                            ->label('PL Promotion Cap')
                                            ->options([
                                                Rank::CADET->value => Rank::CADET->getLabel(),
                                                Rank::PRIVATE->value => Rank::PRIVATE->getLabel(),
                                                Rank::PRIVATE_FIRST_CLASS->value => Rank::PRIVATE_FIRST_CLASS->getLabel(),
                                            ])
                                            ->helperText('Highest rank PLs can promote to without approval'),
                                    ]),

                                Section::make('Locality')
                                    ->description('Update common vernacular to match division needs')
                                    ->collapsible()
                                    ->statePath('settings')
                                    ->schema([
                                        Repeater::make('locality')
                                            ->schema([
                                                TextInput::make('old-string')
                                                    ->label('Replace')
                                                    ->readOnly(),
                                                TextInput::make('new-string')
                                                    ->required()
                                                    ->label('With'),
                                            ])
                                            ->reorderable(false)
                                            ->columns(2)
                                            ->addable(false)
                                            ->deletable(false),
                                    ]),
                            ]),

                        Tab::make('Applications')
                            ->icon('heroicon-o-document-text')
                            ->schema([
                                Toggle::make('settings.application_required')
                                    ->label('Required for recruitment')
                                    ->helperText('New members will be prompted to complete this information during the registration process'),

                                Section::make('Application Fields')
                                    ->description('Define the fields that applicants must complete. Keep it brief and only request what is necessary before recruitment.')
                                    ->schema([
                                        Repeater::make('applicationFields')
                                            ->relationship()
                                            ->orderColumn('display_order')
                                            ->reorderable()
                                            ->hiddenLabel()
                                            ->live()
                                            ->schema([
                                                Select::make('type')
                                                    ->options([
                                                        'text' => 'Single Line',
                                                        'textarea' => 'Multi-Line',
                                                        'checkbox' => 'Checkbox Group',
                                                        'radio' => 'Radio Buttons',
                                                    ])
                                                    ->required()
                                                    ->live(),
                                                TextInput::make('label')
                                                    ->required()
                                                    ->live(onBlur: true),
                                                TextInput::make('helper_text')
                                                    ->label('Helper Text')
                                                    ->live(onBlur: true),
                                                TextInput::make('max_length')
                                                    ->label('Max Length')
                                                    ->numeric()
                                                    ->default(500)
                                                    ->minValue(1)
                                                    ->maxValue(5000)
                                                    ->visible(fn ($get) => in_array($get('type'), ['text', 'textarea'])),
                                                Toggle::make('required')
                                                    ->default(true)
                                                    ->live(),
                                                Repeater::make('options')
                                                    ->schema([
                                                        TextInput::make('label')
                                                            ->required()
                                                            ->hiddenLabel()
                                                            ->live(onBlur: true),
                                                    ])
                                                    ->visible(fn ($get) => in_array($get('type'), ['checkbox', 'radio']))
                                                    ->live()
                                                    ->columnSpanFull(),
                                            ])
                                            ->columns(2)
                                            ->collapsible()
                                            ->itemLabel(fn (array $state): ?string => $state['label'] ?? null),
                                    ]),

                            ]),

                        Tab::make('Induction')
                            ->icon('heroicon-o-user-plus')
                            ->statePath('settings')
                            ->schema([
                                Section::make('Recruiting Tasks')
                                    ->description('Critical steps to perform during recruitment')
                                    ->collapsible()
                                    ->collapsed()
                                    ->schema([
                                        Repeater::make('recruiting_tasks')
                                            ->hiddenLabel()
                                            ->schema([
                                                Textarea::make('task_description')->hiddenLabel(),
                                            ]),
                                    ]),

                                Section::make('Informational Threads')
                                    ->description('Important forum threads for new recruits to be aware of')
                                    ->collapsible()
                                    ->collapsed()
                                    ->schema([
                                        Repeater::make('recruiting_threads')
                                            ->hiddenLabel()
                                            ->schema([
                                                TextInput::make('thread_name')->columnSpanFull(),
                                                TextInput::make('thread_url')
                                                    ->label('Thread URL')
                                                    ->url()
                                                    ->columnSpanFull(),
                                                Textarea::make('comments')->columnSpanFull(),
                                            ]),
                                    ]),

                                Section::make('Welcome Message')
                                    ->schema([
                                        Textarea::make('welcome_pm')
                                            ->label('Welcome DM')
                                            ->rows(6)
                                            ->columnSpanFull()
                                            ->helperText('Available replacement tags (wrap with {{ tag }}): ingame_name, name'),
                                    ]),
                            ]),

                        Tab::make('Notifications')
                            ->icon('heroicon-o-bell')
                            ->schema([
                                Section::make('Channel Configuration')
                                    ->description('Test notification channels from the page header actions')
                                    ->schema([
                                        Placeholder::make('channel_info')
                                            ->hiddenLabel()
                                            ->content(fn (Division $record) => sprintf(
                                                'Officers: %s | Members: %s',
                                                $record->routeNotificationForOfficers() ?? 'Not configured',
                                                $record->routeNotificationForMembers() ?? 'Not configured'
                                            )),
                                    ]),

                                Section::make('Recruitment Notifications')
                                    ->statePath('settings.chat_alerts')
                                    ->columns(3)
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
                                    ]),

                                Section::make('Membership Changes')
                                    ->statePath('settings.chat_alerts')
                                    ->columns(3)
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
                                    ]),

                                Section::make('Administrative Updates')
                                    ->statePath('settings.chat_alerts')
                                    ->columns(3)
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
                                    ]),
                            ]),

                        Tab::make('Website')
                            ->icon('heroicon-o-globe-alt')
                            ->schema([
                                Section::make('Page Content')
                                    ->schema([
                                        MarkdownEditor::make('site_content')
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
                                    ]),

                                Section::make('SEO')
                                    ->schema([
                                        Textarea::make('settings.meta_description')
                                            ->label('Meta Description')
                                            ->maxLength(100)
                                            ->helperText('60-100 character summary of division for SEO purposes. Exposed in URL previews / unfurling.'),
                                    ]),

                                Section::make('Screenshot Gallery')
                                    ->description('Upload screenshots to display on your division page')
                                    ->schema([
                                        FileUpload::make('screenshots')
                                            ->hiddenLabel()
                                            ->multiple()
                                            ->image()
                                            ->disk('public')
                                            ->directory('division-screenshots')
                                            ->maxSize(5120)
                                            ->maxFiles(20)
                                            ->panelLayout('grid')
                                            ->imagePreviewHeight('150')
                                            ->reorderable()
                                            ->appendFiles()
                                            ->openable()
                                            ->downloadable()
                                            ->imageEditor()
                                            ->imageEditorAspectRatios([
                                                '16:9',
                                                '4:3',
                                                '1:1',
                                            ])
                                            ->helperText('Drag to reorder. Click pencil to crop/edit. Max 20 images, 5MB each.'),
                                    ]),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->sortable(),
            ])
            ->filters([
                Filter::make('active')
                    ->query(fn (Builder $query): Builder => $query->where('active', true))->default(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
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
            'index' => ListDivisions::route('/'),
            'edit' => EditDivision::route('/{record}/edit'),
        ];
    }
}
