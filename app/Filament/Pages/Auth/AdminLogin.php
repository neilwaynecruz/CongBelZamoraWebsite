<?php

namespace App\Filament\Pages\Auth;

use Filament\Actions\Action;
use Filament\Forms\Components\Component;
use Filament\Pages\Auth\Login as BaseLogin;
use Illuminate\Contracts\Support\Htmlable;

class AdminLogin extends BaseLogin
{
    protected static string $view = 'filament.pages.auth.admin-login';

    public function getTitle(): string|Htmlable
    {
        return 'District Office Administration';
    }

    public function getHeading(): string|Htmlable
    {
        return 'Secure staff sign in';
    }

    protected function getEmailFormComponent(): Component
    {
        return parent::getEmailFormComponent()
            ->label('Staff email address')
            ->placeholder('name@example.com');
    }

    protected function getPasswordFormComponent(): Component
    {
        return parent::getPasswordFormComponent()
            ->label('Password')
            ->placeholder('Enter your secure password');
    }

    protected function getAuthenticateFormAction(): Action
    {
        return parent::getAuthenticateFormAction()
            ->label('Enter admin console')
            ->icon('heroicon-m-arrow-right-on-rectangle');
    }
}
