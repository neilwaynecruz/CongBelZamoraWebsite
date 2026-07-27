<?php

namespace App\Filament\Resources\ContentApprovalResource\Pages;

use App\Filament\Resources\ContentApprovalResource;
use App\Support\AdminContentInventoryExport;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListContentApprovals extends ListRecords
{
    protected static string $resource = ContentApprovalResource::class;

    public function getTitle(): string
    {
        return 'Content Approvals';
    }

    public function getSubheading(): ?string
    {
        return 'Workflow: Draft/Needs revision → Submit for review → Approve or Reject → Mark published. Use Open review queue for items waiting on reviewers.';
    }

    /**
     * @return array<int, Actions\Action>
     */
    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('export_content_inventory')
                ->label('Export content inventory CSV')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->action(fn () => app(AdminContentInventoryExport::class)->stream()),
        ];
    }
}
