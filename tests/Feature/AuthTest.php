<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'Secret123',
            'password_confirmation' => 'Secret123',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'data' => ['user' => ['id', 'name', 'email', 'role'], 'token'],
            ]);
    }

    public function test_register_fails_with_duplicate_email(): void
    {
        User::factory()->create(['email' => 'john@example.com']);

        $response = $this->postJson('/api/auth/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'Secret123',
            'password_confirmation' => 'Secret123',
        ]);

        $response->assertStatus(422);
    }

    public function test_user_can_login(): void
    {
        User::factory()->create([
            'email' => 'john@example.com',
            'password' => bcrypt('Secret123'),
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'john@example.com',
            'password' => 'Secret123',
        ]);

        $response->assertOk()
            ->assertJsonStructure(['success', 'data' => ['user', 'token']]);
    }

    public function test_login_fails_with_wrong_credentials(): void
    {
        User::factory()->create(['email' => 'john@example.com']);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'john@example.com',
            'password' => 'WrongPassword',
        ]);

        $response->assertStatus(422);
    }

    public function test_authenticated_user_can_get_profile(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson('/api/auth/me');

        $response->assertOk()
            ->assertJsonPath('data.email', $user->email);
    }

    public function test_user_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/auth/logout');

        $response->assertOk();
    }
}
