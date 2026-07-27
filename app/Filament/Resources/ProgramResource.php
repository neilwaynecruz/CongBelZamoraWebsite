<?php

namespace App\Filament\Resources;

use App\Enums\ProgramStatus;
use App\Enums\ResponsibleOfficeType;
use App\Filament\RelationManagers\ContentApprovalsRelationManager;
use App\Filament\RelationManagers\ProgramUpdatesRelationManager;
use App\Filament\RelationManagers\SourceRecordsRelationManager;
use App\Filament\Resources\ProgramResource\Pages;
use App\Filament\Support\SoftDeletableResource;
use App\Models\Program;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Pages\PageRegistration;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ProgramResource extends Resource
{
    use SoftDeletableResource;

    protected static ?string $model = Program::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';

    protected static ?string $navigationGroup = 'Programs & Services';

    protected static ?int $navigationSort = 20;

    protected static ?string $recordTitleAttribute = 'title';

    /**
     * @return list<string>
     */
    public static function getGloballySearchableAttributes(): array
    {
        return ['title', 'summary', 'sector', 'slug'];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Public program')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        Forms\Components\TextInput::make('href')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Select::make('program_category_id')
                            ->relationship('category', 'name')
                            ->searchable()
                            ->preload(),
                        Forms\Components\TextInput::make('sector')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Select::make('status')
                            ->options(ProgramStatus::options())
                            ->required(),
                        Forms\Components\Textarea::make('summary')
                            ->required()
                            ->maxLength(500)
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('description')
                            ->columnSpanFull(),
                    ]),
                Forms\Components\Section::make('Responsibility and source')
                    ->columns(2)
                    ->schema([
                        Forms\Components\Select::make('responsible_office_type')
                            ->options(ResponsibleOfficeType::options())
                            ->required(),
                        Forms\Components\TextInput::make('responsible_office_label')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('partner_agency')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('beneficiary_group')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('application_period')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('benefit')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('source_label')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('source_url')
                            ->url()
                            ->maxLength(255),
                        Forms\Components\DateTimePicker::make('verified_at')
                            ->timezone('Asia/Manila'),
                        Forms\Components\DateTimePicker::make('review_due_at')
                            ->timezone('Asia/Manila'),
                    ]),
                Forms\Components\Section::make('Publication')
                    ->columns(2)
                    ->schema([
                        Forms\Components\Select::make('content_owner_id')
                            ->relationship('contentOwner', 'name')
                            ->searchable()
                            ->preload(),
                        Forms\Components\Select::make('approved_by_id')
                            ->relationship('approver', 'name')
                            ->searchable()
                            ->preload(),
                        Forms\Components\DateTimePicker::make('published_at')
                            ->timezone('Asia/Manila'),
                        Forms\Components\Toggle::make('is_featured'),
                        Forms\Components\TextInput::make('sort_order')
                            ->numeric()
                            ->default(0),
                    ]),
                Forms\Components\Section::make('Requirements')
                    ->schema([
                        Forms\Components\Repeater::make('requirements')
                            ->relationship()
                            ->schema([
                                Forms\Components\TextInput::make('group_title')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('item_text')
                                    ->required()
                                    ->maxLength(500),
                                Forms\Components\TextInput::make('note')
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('sort_order')
                                    ->numeric()
                                    ->default(0),
                            ])
                            ->columns(2)
                            ->defaultItems(0),
                    ]),
                Forms\Components\Section::make('Detail metadata')
                    ->description('Stores detail-page arrays such as eligibility, process steps, reminders, and privacy notes. Use reviewed content only.')
                    ->schema([
                        Forms\Components\KeyValue::make('metadata')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('sector')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->formatStateUsing(fn (ProgramStatus $state): string => $state->label())
                    ->badge()
                    ->color(fn (ProgramStatus $state): string => match ($state) {
                        ProgramStatus::Open => 'success',
                        ProgramStatus::Ongoing => 'info',
                        ProgramStatus::ForAnnouncement => 'warning',
                        ProgramStatus::Closed => 'gray',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('responsible_office_type')
                    ->label('Responsible office')
                    ->formatStateUsing(fn (ResponsibleOfficeType $state): string => $state->label())
                    ->badge()
                    ->color(fn (ResponsibleOfficeType $state): string => match ($state) {
                        ResponsibleOfficeType::DistrictOffice => 'primary',
                        ResponsibleOfficeType::CityGovernment => 'success',
                        ResponsibleOfficeType::NationalAgency => 'warning',
                        ResponsibleOfficeType::JointInitiative => 'info',
                    }),
                Tables\Columns\IconColumn::make('is_featured')
                    ->boolean(),
                Tables\Columns\TextColumn::make('published_at')
                    ->dateTime(timezone: 'Asia/Manila')
                    ->sortable(),
                Tables\Columns\TextColumn::make('review_due_at')
                    ->dateTime(timezone: 'Asia/Manila')
                    ->sortable(),
            ])
            ->defaultSort('sort_order')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(ProgramStatus::options()),
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([]);
    }

    /**
     * @return array<int, class-string>
     */
    public static function getRelations(): array
    {
        return [
            ProgramUpdatesRelationManager::class,
            SourceRecordsRelationManager::class,
            ContentApprovalsRelationManager::class,
        ];
    }

    /**
     * @return array<string, PageRegistration>
     */
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPrograms::route('/'),
            'create' => Pages\CreateProgram::route('/create'),
            'view' => Pages\ViewProgram::route('/{record}'),
            'edit' => Pages\EditProgram::route('/{record}/edit'),
        ];
    }
}
