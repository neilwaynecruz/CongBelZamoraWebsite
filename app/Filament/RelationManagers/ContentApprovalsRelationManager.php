<?php

namespace App\Filament\RelationManagers;

use App\Enums\ContentApprovalStatus;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ContentApprovalsRelationManager extends RelationManager
{
    protected static string $relationship = 'approvalRecords';

    protected static ?string $title = 'Approval Records';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('status')
                ->options(ContentApprovalStatus::options())
                ->required()
                ->native(false),
            Forms\Components\TextInput::make('revision_number')->numeric()->required()->default(1),
            Forms\Components\Select::make('submitted_by_id')->relationship('submitter', 'name')->searchable()->preload(),
            Forms\Components\Select::make('reviewed_by_id')->relationship('reviewer', 'name')->searchable()->preload(),
            Forms\Components\DateTimePicker::make('submitted_at')->timezone('Asia/Manila'),
            Forms\Components\DateTimePicker::make('approved_at')->timezone('Asia/Manila'),
            Forms\Components\DateTimePicker::make('scheduled_for')->timezone('Asia/Manila'),
            Forms\Components\DateTimePicker::make('published_at')->timezone('Asia/Manila'),
            Forms\Components\Textarea::make('review_notes')->columnSpanFull(),
            Forms\Components\Textarea::make('rejection_reason')->columnSpanFull(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('revision_number')->sortable(),
                Tables\Columns\TextColumn::make('status')->badge()->sortable(),
                Tables\Columns\TextColumn::make('submitter.name')->placeholder('Not recorded'),
                Tables\Columns\TextColumn::make('scheduled_for')->dateTime(timezone: 'Asia/Manila')->sortable(),
                Tables\Columns\TextColumn::make('published_at')->dateTime(timezone: 'Asia/Manila')->sortable(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([]);
    }
}
