<?php

namespace App\Filament\Support;

use App\Models\User;
use Filament\Actions;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

trait SoftDeleteHeaderActions
{
    /**
     * @return array<int, Actions\Action>
     */
    protected function softDeleteHeaderActions(): array
    {
        /** @var Model $record */
        $record = $this->getRecord();
        $user = Auth::user();

        if (! $user instanceof User || ! in_array(SoftDeletes::class, class_uses_recursive($record), true)) {
            return [
                Actions\DeleteAction::make(),
            ];
        }

        return [
            Actions\DeleteAction::make()
                ->visible(fn (): bool => ! $this->isSoftDeleted($record) && $user->can('delete', $record)),
            Actions\RestoreAction::make()
                ->visible(fn (): bool => $this->isSoftDeleted($record) && $user->can('restore', $record)),
            Actions\ForceDeleteAction::make()
                ->visible(fn (): bool => $this->isSoftDeleted($record) && $user->can('forceDelete', $record)),
        ];
    }

    private function isSoftDeleted(Model $record): bool
    {
        return method_exists($record, 'trashed') && $record->trashed();
    }
}
