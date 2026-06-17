<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DemoSeedSmokeTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeded_demo_users_can_login_and_reach_role_dashboards(): void
    {
        $this->seed();

        $destinations = [
            'admin_hr@example.com' => '/admin/dashboard',
            'supervisor@example.com' => '/supervisor/dashboard',
            'employee@example.com' => '/employee/dashboard',
            'management@example.com' => '/management/dashboard',
            'it@example.com' => '/it/dashboard',
        ];

        foreach ($destinations as $email => $path) {
            $response = $this->post('/login', [
                'email' => $email,
                'password' => 'password',
            ]);

            $response->assertRedirect($path);
            $this->get($path)->assertOk();
            $this->post('/logout')->assertRedirect('/');
        }
    }
}
