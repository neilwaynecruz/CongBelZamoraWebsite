<?php

namespace Tests\Feature;

use App\Enums\AdminRole;
use App\Models\AuditLog;
use App\Models\User;
use App\Services\AdminConsoleNavigationService;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AdminConsoleUsersSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_and_settings_console_are_super_administrator_only(): void
    {
        $superAdministrator = User::factory()
            ->adminRole(AdminRole::SuperAdministrator)
            ->withTwoFactor()
            ->create();
        $programOfficer = User::factory()->adminRole(AdminRole::ProgramOfficer)->create();

        $this->actingAs($superAdministrator)
            ->get(route('admin-console.users.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Users/Index')
                ->where('adminConsole.user.role', AdminRole::SuperAdministrator->value)
                ->where('can.create', true));

        $this->actingAs($superAdministrator)
            ->get(route('admin-console.settings'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Settings/Index')
                ->where('writeAccess.enabled', false));

        $labels = collect(app(AdminConsoleNavigationService::class)->for($superAdministrator))
            ->pluck('items')
            ->flatten(1)
            ->pluck('label');

        $this->assertTrue($labels->contains('Staff Users'));
        $this->assertTrue($labels->contains('Settings'));

        $this->actingAs($programOfficer)
            ->get(route('admin-console.users.index'))
            ->assertForbidden();

        $this->actingAs($programOfficer)
            ->get(route('admin-console.settings'))
            ->assertForbidden();
    }

    public function test_staff_user_editor_routes_are_policy_protected(): void
    {
        $superAdministrator = User::factory()
            ->adminRole(AdminRole::SuperAdministrator)
            ->withTwoFactor()
            ->create();
        $programOfficer = User::factory()->adminRole(AdminRole::ProgramOfficer)->create();
        $staffUser = User::factory()->adminRole(AdminRole::CommunicationsEditor)->create([
            'name' => 'Communications Staff',
        ]);

        $this->actingAs($superAdministrator)
            ->get(route('admin-console.users.create'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Users/Edit')
                ->where('form.mode', 'create')
                ->where('form.twoFactorRequired', false));

        $this->actingAs($superAdministrator)
            ->get(route('admin-console.users.edit', $staffUser))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Users/Edit')
                ->where('form.mode', 'edit')
                ->where('form.values.name', 'Communications Staff'));

        $this->actingAs($programOfficer)
            ->get(route('admin-console.users.create'))
            ->assertForbidden();

        $this->actingAs($programOfficer)
            ->get(route('admin-console.users.edit', $staffUser))
            ->assertForbidden();
    }

    public function test_staff_user_editor_validates_required_and_sensitive_fields(): void
    {
        $superAdministrator = User::factory()
            ->adminRole(AdminRole::SuperAdministrator)
            ->withTwoFactor()
            ->create();

        $this->actingAs($superAdministrator)
            ->post(route('admin-console.users.store'), [
                'name' => '',
                'email' => 'not-an-email',
                'admin_role' => 'district_overlord',
                'is_active' => true,
                'password' => 'short',
            ])
            ->assertSessionHasErrors([
                'name',
                'email',
                'admin_role',
                'password',
            ]);
    }

    public function test_staff_user_editor_creates_staff_user_with_safe_audit_values(): void
    {
        $superAdministrator = User::factory()
            ->adminRole(AdminRole::SuperAdministrator)
            ->withTwoFactor()
            ->create();

        $this->actingAs($superAdministrator)
            ->post(route('admin-console.users.store'), [
                'name' => 'District Programs Lead',
                'email' => 'programs.lead@example.test',
                'admin_role' => AdminRole::ProgramOfficer->value,
                'is_active' => true,
                'password' => 'VeryStrongPassword123!',
            ])
            ->assertRedirect();

        $staffUser = User::query()
            ->where('email', 'programs.lead@example.test')
            ->firstOrFail();

        $this->assertSame(AdminRole::ProgramOfficer, $staffUser->admin_role);
        $this->assertTrue($staffUser->is_active);
        $this->assertNotNull($staffUser->email_verified_at);
        $this->assertTrue(Hash::check('VeryStrongPassword123!', $staffUser->password));

        $audit = AuditLog::query()
            ->where('auditable_type', $staffUser->getMorphClass())
            ->where('auditable_id', $staffUser->id)
            ->where('event', 'staff_user.created')
            ->firstOrFail();

        $this->assertSame(true, $audit->new_values['password_changed']);
        $this->assertArrayNotHasKey('password', $audit->new_values);
        $this->assertArrayNotHasKey('two_factor_secret', $audit->new_values);
        $this->assertArrayNotHasKey('two_factor_recovery_codes', $audit->new_values);
    }

    public function test_staff_user_editor_updates_role_status_and_password_with_audit_logging(): void
    {
        $superAdministrator = User::factory()->adminRole(AdminRole::SuperAdministrator)->create();
        $staffUser = User::factory()->adminRole(AdminRole::ProgramOfficer)->create([
            'name' => 'Existing Staff',
            'email' => 'existing.staff@example.test',
        ]);

        $this->actingAs($superAdministrator)
            ->put(route('admin-console.users.update', $staffUser), [
                'name' => 'Updated Staff',
                'email' => 'updated.staff@example.test',
                'admin_role' => AdminRole::ContentAdministrator->value,
                'is_active' => false,
                'password' => 'ReplacementPassword123!',
            ])
            ->assertRedirect(route('admin-console.users.show', $staffUser));

        $staffUser->refresh();

        $this->assertSame('Updated Staff', $staffUser->name);
        $this->assertSame('updated.staff@example.test', $staffUser->email);
        $this->assertSame(AdminRole::ContentAdministrator, $staffUser->admin_role);
        $this->assertFalse($staffUser->is_active);
        $this->assertTrue(Hash::check('ReplacementPassword123!', $staffUser->password));

        $this->assertDatabaseHas('audit_logs', [
            'auditable_type' => $staffUser->getMorphClass(),
            'auditable_id' => $staffUser->id,
            'event' => 'staff_user.updated',
        ]);
    }

    public function test_super_administrator_cannot_deactivate_or_demote_their_own_account(): void
    {
        $superAdministrator = User::factory()->adminRole(AdminRole::SuperAdministrator)->create([
            'email' => 'super@example.test',
        ]);

        $this->actingAs($superAdministrator)
            ->put(route('admin-console.users.update', $superAdministrator), [
                'name' => $superAdministrator->name,
                'email' => $superAdministrator->email,
                'admin_role' => AdminRole::SuperAdministrator->value,
                'is_active' => false,
                'password' => '',
            ])
            ->assertSessionHasErrors('is_active');

        $this->actingAs($superAdministrator)
            ->put(route('admin-console.users.update', $superAdministrator), [
                'name' => $superAdministrator->name,
                'email' => $superAdministrator->email,
                'admin_role' => AdminRole::ProgramOfficer->value,
                'is_active' => true,
                'password' => '',
            ])
            ->assertSessionHasErrors('admin_role');
    }

    public function test_users_console_filters_by_search_role_status_and_two_factor_state(): void
    {
        Config::set('belzamora.staff.require_2fa', true);

        $superAdministrator = User::factory()
            ->adminRole(AdminRole::SuperAdministrator)
            ->withTwoFactor()
            ->create();
        User::factory()
            ->adminRole(AdminRole::ProgramOfficer)
            ->withTwoFactor()
            ->create([
                'name' => 'Searchable Program Lead',
                'email' => 'searchable.program@example.test',
                'is_active' => true,
            ]);
        User::factory()->adminRole(AdminRole::ProgramOfficer)->create([
            'name' => 'Missing Two Factor Lead',
            'email' => 'missing.2fa@example.test',
            'is_active' => true,
        ]);
        User::factory()->adminRole(AdminRole::CommunicationsEditor)->create([
            'name' => 'Inactive Communications Lead',
            'email' => 'inactive.comms@example.test',
            'is_active' => false,
        ]);
        User::factory()->create([
            'name' => 'Public Non Staff',
            'email' => 'public@example.test',
            'admin_role' => null,
        ]);

        $this->actingAs($superAdministrator)
            ->get(route('admin-console.users.index', [
                'search' => 'Searchable',
                'role' => AdminRole::ProgramOfficer->value,
                'status' => 'active',
                'two_factor' => 'enabled',
            ]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Users/Index')
                ->where('staffUsers.data.0.name', 'Searchable Program Lead')
                ->where('staffUsers.data.0.twoFactor.compliant', true)
                ->where('staffUsers.meta.total', 1)
                ->where('filters.search', 'Searchable')
                ->where('filters.role', AdminRole::ProgramOfficer->value)
                ->where('security.twoFactorRequired', true));
    }

    public function test_staff_user_dossier_can_send_password_reset_without_exposing_secrets(): void
    {
        Notification::fake();

        $superAdministrator = User::factory()->adminRole(AdminRole::SuperAdministrator)->create();
        $staffUser = User::factory()
            ->adminRole(AdminRole::InquiryOfficer)
            ->withTwoFactor()
            ->create(['email' => 'inquiry.staff@example.test']);

        $this->actingAs($superAdministrator)
            ->get(route('admin-console.users.show', $staffUser))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Users/Show')
                ->where('staffUser.email', 'inquiry.staff@example.test')
                ->where('staffUser.twoFactor.enabled', true)
                ->missing('staffUser.password')
                ->missing('staffUser.two_factor_secret')
                ->missing('staffUser.two_factor_recovery_codes'));

        $this->actingAs($superAdministrator)
            ->post(route('admin-console.users.password-reset', $staffUser))
            ->assertRedirect();

        Notification::assertSentTo($staffUser, ResetPassword::class);
        $this->assertDatabaseHas('audit_logs', [
            'auditable_type' => $staffUser->getMorphClass(),
            'auditable_id' => $staffUser->id,
            'event' => 'staff_user.password_reset_link_sent',
        ]);
    }

    public function test_settings_page_exposes_read_only_launch_and_security_configuration(): void
    {
        Config::set('belzamora.staff.require_2fa', true);
        Config::set('belzamora.admin_console.filament_fallback_enabled', true);

        $superAdministrator = User::factory()
            ->adminRole(AdminRole::SuperAdministrator)
            ->withTwoFactor()
            ->create();

        $this->actingAs($superAdministrator)
            ->get(route('admin-console.settings'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Settings/Index')
                ->where('settings.staffSecurity.twoFactorRequired', true)
                ->where('settings.adminConsole.filamentFallbackEnabled', true)
                ->where('writeAccess.enabled', false)
                ->where('filamentFallbackUrl', url('/admin')));
    }

    public function test_command_palette_search_routes_staff_users_to_custom_dossier(): void
    {
        $superAdministrator = User::factory()->adminRole(AdminRole::SuperAdministrator)->create();
        $staffUser = User::factory()->adminRole(AdminRole::ProjectEditor)->create([
            'name' => 'Custom Searchable Staff',
        ]);

        $this->actingAs($superAdministrator)
            ->getJson(route('admin-console.search', ['term' => 'Custom Searchable Staff']))
            ->assertOk()
            ->assertJsonFragment([
                'type' => 'Staff User',
                'title' => 'Custom Searchable Staff',
                'href' => route('admin-console.users.show', $staffUser),
                'usesLegacyFallback' => false,
            ]);
    }
}
