<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\AdminRole;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Laravel\Fortify\Contracts\PasskeyUser;
use Laravel\Fortify\PasskeyAuthenticatable;
use Laravel\Fortify\TwoFactorAuthenticatable;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property Carbon|null $email_verified_at
 * @property string $password
 * @property AdminRole|null $admin_role
 * @property string|null $two_factor_secret
 * @property string|null $two_factor_recovery_codes
 * @property Carbon|null $two_factor_confirmed_at
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['name', 'email', 'password', 'admin_role'])]
#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]
class User extends Authenticatable implements FilamentUser, PasskeyUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, PasskeyAuthenticatable, TwoFactorAuthenticatable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'admin_role' => AdminRole::class,
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
        ];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->isStaff();
    }

    public function isStaff(): bool
    {
        return $this->admin_role instanceof AdminRole;
    }

    public function hasAdminRole(AdminRole ...$roles): bool
    {
        return $this->admin_role instanceof AdminRole
            && in_array($this->admin_role, $roles, true);
    }

    public function canManageInquiries(): bool
    {
        return $this->hasAdminRole(
            AdminRole::SuperAdministrator,
            AdminRole::InquiryOfficer,
        );
    }

    public function canAuditRecords(): bool
    {
        return $this->hasAdminRole(
            AdminRole::SuperAdministrator,
            AdminRole::ReadOnlyAuditor,
        );
    }

    public function canManagePrograms(): bool
    {
        return $this->hasAdminRole(
            AdminRole::SuperAdministrator,
            AdminRole::ContentAdministrator,
            AdminRole::ProgramOfficer,
        );
    }

    public function canManageLegislation(): bool
    {
        return $this->hasAdminRole(
            AdminRole::SuperAdministrator,
            AdminRole::ContentAdministrator,
            AdminRole::LegislativeEditor,
        );
    }

    public function canManageProjects(): bool
    {
        return $this->hasAdminRole(
            AdminRole::SuperAdministrator,
            AdminRole::ContentAdministrator,
            AdminRole::ProjectEditor,
        );
    }

    public function canManageNewsMedia(): bool
    {
        return $this->hasAdminRole(
            AdminRole::SuperAdministrator,
            AdminRole::ContentAdministrator,
            AdminRole::CommunicationsEditor,
            AdminRole::MediaManager,
        );
    }

    public function canReviewPublicContent(): bool
    {
        return $this->hasAdminRole(
            AdminRole::SuperAdministrator,
            AdminRole::ContentAdministrator,
            AdminRole::ReviewerApprover,
        );
    }

    /**
     * @return HasMany<Inquiry, $this>
     */
    public function assignedInquiries(): HasMany
    {
        return $this->hasMany(Inquiry::class, 'assigned_officer_id');
    }
}
