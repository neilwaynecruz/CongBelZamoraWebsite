<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static ?string $title = 'Operations Command Center';

    protected static ?string $navigationIcon = 'heroicon-o-home';

    protected static ?string $navigationLabel = 'Command Center';

    protected static ?int $navigationSort = -10;

    /**
     * @return array<string, int|string|null>
     */
    public function getColumns(): array
    {
        return [
            'default' => 1,
            'md' => 2,
            'xl' => 4,
        ];
    }
}
