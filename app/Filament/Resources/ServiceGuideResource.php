<?php

namespace App\Filament\Resources;

use App\Filament\Support\SoftDeletableResource;

use App\Enums\ResponsibleOfficeType;
use App\Filament\Resources\ServiceGuideResource\Pages;
use App\Filament\RelationManagers\ContentApprovalsRelationManager;
use App\Filament\RelationManagers\SourceRecordsRelationManager;
use App\Models\ServiceGuide;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Pages\PageRegistration;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ServiceGuideResource extends Resource
{
    use SoftDeletableResource;

    protected static ?string $model = ServiceGuide::class;

    protected static ?string $navigationIcon = 'heroicon-o-map';

    protected static ?string $navigationGroup = 'Programs & Services';

    protected static ?int $navigationSort = 30;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Service guide')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        Forms\Components\TextInput::make('label')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('concern')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Select::make('responsible_office_type')
                            ->options(ResponsibleOfficeType::options())
                            ->required(),
                        Forms\Components\TextInput::make('responsible_office_label')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Textarea::make('district_office_role')
                            ->required()
                            ->columnSpanFull(),
                        Forms\Components\Toggle::make('can_district_office_directly_handle'),
                        Forms\Components\Textarea::make('explanation')
                            ->required()
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('next_step')
                            ->required()
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('contact_label')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('contact_href')
                            ->required()
                            ->maxLength(255),
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
                        Forms\Components\DateTimePicker::make('verified_at')
                            ->timezone('Asia/Manila'),
                        Forms\Components\DateTimePicker::make('published_at')
                            ->timezone('Asia/Manila'),
                        Forms\Components\DateTimePicker::make('review_due_at')
                            ->timezone('Asia/Manila'),
                        Forms\Components\Toggle::make('is_featured'),
                        Forms\Components\TextInput::make('sort_order')
                            ->numeric()
                            ->default(0),
                    ]),
                Forms\Components\Section::make('Guide checklist items')
                    ->schema([
                        Forms\Components\Repeater::make('steps')
                            ->relationship()
                            ->schema([
                                Forms\Components\Select::make('section')
                                    ->options([
                                        'prepare' => 'Prepare',
                                        'do_not_submit' => 'Do not submit',
                                    ])
                                    ->required(),
                                Forms\Components\TextInput::make('item_text')
                                    ->required()
                                    ->maxLength(500),
                                Forms\Components\TextInput::make('sort_order')
                                    ->numeric()
                                    ->default(0),
                            ])
                            ->columns(3)
                            ->defaultItems(0),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('label')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('responsible_office_type')
                    ->formatStateUsing(fn (ResponsibleOfficeType $state): string => $state->label())
                    ->badge()
                    ->color(fn (ResponsibleOfficeType $state): string => match ($state) {
                        ResponsibleOfficeType::DistrictOffice => 'primary',
                        ResponsibleOfficeType::CityGovernment => 'success',
                        ResponsibleOfficeType::NationalAgency => 'warning',
                        ResponsibleOfficeType::JointInitiative => 'info',
                    }),
                Tables\Columns\IconColumn::make('can_district_office_directly_handle')
                    ->boolean(),
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
                Tables\Filters\SelectFilter::make('responsible_office_type')
                    ->options(ResponsibleOfficeType::options()),
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
            'index' => Pages\ListServiceGuides::route('/'),
            'create' => Pages\CreateServiceGuide::route('/create'),
            'view' => Pages\ViewServiceGuide::route('/{record}'),
            'edit' => Pages\EditServiceGuide::route('/{record}/edit'),
        ];
    }
}
