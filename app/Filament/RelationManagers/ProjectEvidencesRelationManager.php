<?php

namespace App\Filament\RelationManagers;

use App\Support\ApprovedMediaForm;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ProjectEvidencesRelationManager extends RelationManager
{
    protected static string $relationship = 'evidences';

    protected static ?string $title = 'Project Evidence';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('title')->required()->maxLength(255),
            Forms\Components\TextInput::make('source_label')->required()->maxLength(255),
            Forms\Components\TextInput::make('source_url')->required()->url()->maxLength(255),
            ...ApprovedMediaForm::uploadFields(
                directoryConfigKey: 'project_evidence_directory',
                sectionDescription: 'Upload only rights-cleared official activity images. Do not upload citizen ID scans or confidential documents.',
            ),
            Forms\Components\DateTimePicker::make('verified_at')->timezone('Asia/Manila'),
            Forms\Components\Select::make('verified_by_id')->relationship('verifier', 'name')->searchable()->preload(),
            Forms\Components\TextInput::make('sort_order')->numeric()->default(0),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('media_path')->disk('public')->square()->label('Image'),
                Tables\Columns\TextColumn::make('title')->searchable()->wrap(),
                Tables\Columns\TextColumn::make('source_label')->label('Source')->searchable(),
                Tables\Columns\TextColumn::make('verified_at')->dateTime(timezone: 'Asia/Manila')->sortable(),
            ])
            ->headerActions([Tables\Actions\CreateAction::make()])
            ->actions([Tables\Actions\EditAction::make()])
            ->bulkActions([]);
    }
}
