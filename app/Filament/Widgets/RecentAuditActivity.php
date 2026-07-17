<?php

namespace App\Filament\Widgets;

use App\Models\AuditLog;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class RecentAuditActivity extends TableWidget
{
    protected static ?string $heading = 'Recent admin activity';

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query($this->query())
            ->columns([
                Tables\Columns\TextColumn::make('event')
                    ->searchable(),
                Tables\Columns\TextColumn::make('actor.name')
                    ->label('Actor')
                    ->placeholder('System'),
                Tables\Columns\TextColumn::make('auditable_type')
                    ->label('Record type'),
                Tables\Columns\TextColumn::make('occurred_at')
                    ->dateTime(timezone: 'Asia/Manila')
                    ->sortable(),
            ])
            ->defaultSort('occurred_at', 'desc')
            ->paginated([10, 25]);
    }

    /**
     * @return Builder<AuditLog>
     */
    private function query(): Builder
    {
        return AuditLog::query()->with('actor');
    }
}
