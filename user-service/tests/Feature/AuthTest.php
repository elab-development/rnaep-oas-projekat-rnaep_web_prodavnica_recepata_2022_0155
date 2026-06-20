<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Vanja',
            'email' => 'vanja@test.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'user',
                'token',
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'vanja@test.com',
            'role' => 'user',
        ]);
    }

    public function test_user_cannot_register_with_existing_email(): void
    {
        User::create([
            'name' => 'Postojeci korisnik',
            'email' => 'vanja@test.com',
            'password' => Hash::make('password123'),
            'role' => 'user',
        ]);

        $response = $this->postJson('/api/register', [
            'name' => 'Vanja',
            'email' => 'vanja@test.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(422);
    }

    public function test_user_can_login_with_valid_credentials(): void
    {
        User::create([
            'name' => 'Vanja',
            'email' => 'vanja@test.com',
            'password' => Hash::make('password123'),
            'role' => 'user',
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'vanja@test.com',
            'password' => 'password123',
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'message',
                'user',
                'token',
            ]);
    }

    public function test_user_cannot_login_with_wrong_password(): void
    {
        User::create([
            'name' => 'Vanja',
            'email' => 'vanja@test.com',
            'password' => Hash::make('password123'),
            'role' => 'user',
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'vanja@test.com',
            'password' => 'pogresna-lozinka',
        ]);

        $response->assertStatus(422);
    }

    public function test_authenticated_user_can_be_verified(): void
    {
        $user = User::create([
            'name' => 'Admin',
            'email' => 'admin@test.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/verify');

        $response->assertOk()
            ->assertJson([
                'email' => 'admin@test.com',
                'role' => 'admin',
            ]);
    }

    public function test_verify_route_requires_authentication(): void
    {
        $response = $this->getJson('/api/verify');

        $response->assertStatus(401);
    }
}