<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AuditLogResource\Pages;
use App\Models\AuditLog;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Pages\PageRegistration;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AuditLogResource extends Resource
{
    protected static ?string $model = AuditLog::class;

    protected static ?string $navigationIcon = 'heroicon-o-shield-check';

    protected static ?string $navigationGroup = 'Governance';

    protected static ?int $navigationSort = 90;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Audit event')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('event')
                            ->disabled(),
                        Forms\Components\TextInput::make('actor.name')
                            ->label('Actor')
                            ->disabled(),
                        Forms\Components\TextInput::make('auditable_type')
                            ->disabled(),
                        Forms\Components\TextInput::make('auditable_id')
                            ->disabled(),
                        Forms\Components\DateTimePicker::make('occurred_at')
                            ->timezone('Asia/Manila')
                            ->disabled(),
                    ]),
                Forms\Components\Section::make('Redacted values')
                    ->schema([
                        Forms\Components\KeyValue::make('old_values')
                            ->disabled(),
                        Forms\Components\KeyValue::make('new_values')
                            ->disabled(),
                        Forms\Components\KeyValue::make('metadata')
                            ->disabled(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('event')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('actor.name')
                    ->label('Actor')
                    ->placeholder('System')
                    ->searchable(),
                Tables\Columns\TextColumn::make('auditable_type')
                    ->label('Record type')
                    ->searchable(),
                Tables\Columns\TextColumn::make('auditable_id')
                    ->label('Record ID')
                    ->sortable(),
                Tables\Columns\TextColumn::make('occurred_at')
                    ->dateTime(timezone: 'Asia/Manila')
                    ->sortable(),
            ])
            ->defaultSort('occurred_at', 'desc')
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([]);
    }

    /**
     * @return array<string, PageRegistration>
     */
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAuditLogs::route('/'),
            'view' => Pages\ViewAuditLog::route('/{record}'),
        ];
    }
}
