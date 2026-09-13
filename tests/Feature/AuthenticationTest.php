<?php

namespace Tests\Feature;

use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_can_log_in_with_a_username_and_reaches_the_student_dashboard(): void
    {
        $student = User::factory()->create([
            'username' => 'student-001',
            'role' => 'student',
            'password' => 'password',
        ]);

        $response = $this->post(route('login.store'), [
            'login' => 'student-001',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('student.dashboard'));
        $this->assertAuthenticatedAs($student, 'web');
    }

    public function test_teacher_can_log_in_with_an_email_and_reaches_the_teacher_dashboard(): void
    {
        $teacher = User::factory()->teacher()->create([
            'email' => 'teacher@example.test',
            'password' => 'password',
        ]);

        $response = $this->post(route('login.store'), [
            'login' => 'teacher@example.test',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('teacher.dashboard'));
        $this->assertAuthenticatedAs($teacher, 'web');
    }

    public function test_student_login_page_cannot_authenticate_a_teacher(): void
    {
        User::factory()->teacher()->create([
            'username' => 'teacher-001',
            'password' => 'password',
        ]);

        $response = $this->from(route('student.login'))->post(route('login.store'), [
            'login' => 'teacher-001',
            'password' => 'password',
            'role' => 'student',
        ]);

        $response->assertRedirect(route('student.login'));
        $this->assertGuest('web');
    }

    public function test_inactive_users_cannot_log_in(): void
    {
        User::factory()->create([
            'username' => 'inactive-001',
            'is_active' => false,
            'password' => 'password',
        ]);

        $this->post(route('login.store'), [
            'login' => 'inactive-001',
            'password' => 'password',
        ])->assertSessionHasErrors('login');

        $this->assertGuest('web');
    }

    public function test_role_middleware_blocks_other_roles(): void
    {
        $teacher = User::factory()->teacher()->create();

        $this->actingAs($teacher, 'web')
            ->get(route('student.dashboard'))
            ->assertForbidden();
    }

    public function test_application_uses_one_web_guard(): void
    {
        $this->assertSame(['web'], array_keys(config('auth.guards')));
        $this->assertSame('web', config('auth.defaults.guard'));
    }

    public function test_only_an_active_super_admin_can_access_the_admin_panel(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $inactiveAdmin = User::factory()->superAdmin()->create(['is_active' => false]);
        $teacher = User::factory()->teacher()->create();
        $panel = Filament::getPanel('admin');

        $this->assertTrue($superAdmin->canAccessPanel($panel));
        $this->assertFalse($inactiveAdmin->canAccessPanel($panel));
        $this->assertFalse($teacher->canAccessPanel($panel));
    }

    public function test_super_admin_can_open_the_filament_panel_but_teacher_cannot(): void
    {
        $this->actingAs(User::factory()->superAdmin()->create(), 'web')
            ->get('/admin')
            ->assertOk();

        $this->actingAs(User::factory()->teacher()->create(), 'web')
            ->get('/admin')
            ->assertForbidden();
    }

    public function test_super_admin_can_open_a_management_resource(): void
    {
        $this->actingAs(User::factory()->superAdmin()->create(), 'web')
            ->get('/admin/students')
            ->assertOk();
    }

    public function test_student_policy_only_allows_a_student_to_view_their_own_profile(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $otherStudent = User::factory()->create(['role' => 'student']);
        $studentProfile = $student->student()->create(['admission_no' => 'ADM-001']);
        $otherProfile = $otherStudent->student()->create(['admission_no' => 'ADM-002']);

        $this->assertTrue(Gate::forUser($student)->allows('view', $studentProfile));
        $this->assertFalse(Gate::forUser($student)->allows('view', $otherProfile));
    }

    public function test_filament_login_page_loads_successfully(): void
    {
        $this->get('/admin/login')
            ->assertOk()
            ->assertSee('filament.pages.auth.login');
    }

    public function test_super_admin_can_authenticate_via_filament_livewire_login(): void
    {
        $superAdmin = User::factory()->superAdmin()->create([
            'email' => 'superadmin@example.test',
            'password' => 'password',
        ]);

        \Livewire\Livewire::test(\Filament\Pages\Auth\Login::class)
            ->set('data.email', 'superadmin@example.test')
            ->set('data.password', 'password')
            ->call('authenticate')
            ->assertHasNoErrors()
            ->assertRedirect(Filament::getUrl());

        $this->assertAuthenticatedAs($superAdmin, 'web');
    }
}
