<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_role_dashboards_render_role_specific_content(): void
    {
        $dashboards = [
            ['role' => 'admin_hr', 'url' => '/admin/dashboard', 'text' => 'Admin HR Dashboard'],
            ['role' => 'management', 'url' => '/management/dashboard', 'text' => 'Management Dashboard'],
            ['role' => 'supervisor', 'url' => '/supervisor/dashboard', 'text' => 'Supervisor Dashboard'],
            ['role' => 'employee', 'url' => '/employee/dashboard', 'text' => 'Employee Dashboard'],
            ['role' => 'it_admin', 'url' => '/it/dashboard', 'text' => 'IT Admin Dashboard'],
        ];

        foreach ($dashboards as $dashboard) {
            $user = User::factory()->create(['role' => $dashboard['role']]);

            $this->actingAs($user)
                ->get($dashboard['url'])
                ->assertOk()
                ->assertSee($dashboard['text']);
        }
    }
}
