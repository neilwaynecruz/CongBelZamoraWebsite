<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AuditLogResource\Pages;
use App\Models\AuditLog;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Pages\PageRegistration;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AuditLogResource extends Resource
{
    protected static ?string $model = AuditLog::class;

    protected static ?string $navigationIcon = 'heroicon-o-shield-check';

    protected static ?string $navigationGroup = 'Governance & Audit';

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
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'created' => 'success',
                        'updated' => 'warning',
                        'deleted' => 'danger',
                        default => 'info',
                    })
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('actor.name')
                    ->label('Actor')
                    ->placeholder('System')
                    ->searchable(),
                Tables\Columns\TextColumn::make('auditable_type')
                    ->label('Record Type')
                    ->formatStateUsing(fn (string $state): string => str(class_basename($state))->headline())
                    ->searchable(),
                Tables\Columns\TextColumn::make('auditable_id')
                    ->label('Record ID')
                    ->sortable(),
                Tables\Columns\TextColumn::make('occurred_at')
                    ->dateTime(format: 'M j, Y g:i A', timezone: 'Asia/Manila')
                    ->sortable(),
            ])
            ->defaultSort('occurred_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('event')
                    ->options(fn (): array => AuditLog::query()
                        ->select('event')
                        ->distinct()
                        ->orderBy('event')
                        ->pluck('event', 'event')
                        ->all()),
                Tables\Filters\SelectFilter::make('auditable_type')
                    ->label('Record type')
                    ->options(fn (): array => AuditLog::query()
                        ->select('auditable_type')
                        ->whereNotNull('auditable_type')
                        ->distinct()
                        ->orderBy('auditable_type')
                        ->pluck('auditable_type', 'auditable_type')
                        ->mapWithKeys(fn (string $type): array => [$type => str(class_basename($type))->headline()->toString()])
                        ->all()),
                Tables\Filters\Filter::make('actor_is_me')
                    ->label('Actor is me')
                    ->query(fn (Builder $query): Builder => $query->where('actor_user_id', Auth::id())),
                Tables\Filters\Filter::make('system_events')
                    ->label('System events')
                    ->query(fn (Builder $query): Builder => $query->whereNull('actor_user_id')),
                Tables\Filters\Filter::make('occurred_between')
                    ->form([
                        Forms\Components\DatePicker::make('from')->label('From'),
                        Forms\Components\DatePicker::make('until')->label('Until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'] ?? null, fn (Builder $query, string $date): Builder => $query->whereDate('occurred_at', '>=', $date))
                            ->when($data['until'] ?? null, fn (Builder $query, string $date): Builder => $query->whereDate('occurred_at', '<=', $date));
                    }),
            ])
            ->headerActions([
                Tables\Actions\Action::make('export_audit_summary')
                    ->label('Export audit summary CSV')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('gray')
                    ->action(fn () => self::exportAuditSummaryCsv(AuditLog::query()->with('actor'))),
            ])
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

    /**
     * @param  Builder<AuditLog>  $query
     */
    public static function exportAuditSummaryCsv(Builder $query): StreamedResponse
    {
        $filename = 'audit-summary-'.now('Asia/Manila')->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($query): void {
            $handle = fopen('php://output', 'w');

            if ($handle === false) {
                return;
            }

            fputcsv($handle, ['event', 'actor', 'record_type', 'record_id', 'occurred_at']);

            $query->orderByDesc('occurred_at')->chunk(200, function ($logs) use ($handle): void {
                foreach ($logs as $log) {
                    /** @var AuditLog $log */
                    $actor = $log->getRelationValue('actor');

                    fputcsv($handle, [
                        $log->event,
                        $actor instanceof User ? $actor->name : 'System',
                        $log->auditable_type !== null ? str(class_basename($log->auditable_type))->headline()->toString() : '',
                        $log->auditable_id,
                        $log->occurred_at->timezone('Asia/Manila')->toDateTimeString(),
                    ]);
                }
            });

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
