<?php

namespace App\Filament\Widgets;

use App\Models\AuditLog;
use App\Models\User;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class RecentAuditActivity extends TableWidget
{
    protected static ?string $heading = 'Recent Admin & Audit Activity';

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = -20; // Positioned below NeedsAttentionWidget

    public static function canView(): bool
    {
        $user = Auth::user();

        return $user instanceof User && $user->canAuditRecords();
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->query())
            ->columns([
                Tables\Columns\TextColumn::make('event')
                    ->badge()
                    ->color(fn (mixed $state): string => match ((string) $state) {
                        'created' => 'success',
                        'updated' => 'warning',
                        'deleted' => 'danger',
                        'admin_dashboard_health_check_run' => 'info',
                        default => 'info',
                    })
                    ->formatStateUsing(fn (mixed $state): string => Str::of((string) $state)
                        ->replace('_', ' ')
                        ->headline()
                        ->toString())
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('actor.name')
                    ->label('Actor')
                    ->placeholder('System')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('auditable_type')
                    ->label('Record Type')
                    ->formatStateUsing(fn (mixed $state): string => filled($state)
                        ? Str::of(class_basename((string) $state))->headline()->toString()
                        : 'System')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('occurred_at')
                    ->label('Time Occurred')
                    ->dateTime(format: 'M j, Y g:i A', timezone: 'Asia/Manila')
                    ->sortable(),
            ])
            ->defaultSort('occurred_at', 'desc')
            ->paginated([5, 10, 25])
            ->emptyStateHeading('No recent activity')
            ->emptyStateDescription('Audit log is currently empty.');
    }

    /**
     * @return Builder<AuditLog>
     */
    private function query(): Builder
    {
        return AuditLog::query()->with('actor');
    }
}
