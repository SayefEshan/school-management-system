<?php

namespace Modules\User\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UserApiTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private Role $tenantRole;

    protected function setUp(): void
    {
        parent::setUp();

        // Create roles
        $this->tenantRole = Role::create(['name' => 'tenant', 'guard_name' => 'web']);
        Role::create(['name' => 'admin', 'guard_name' => 'web']);

        // Create admin user
        $this->admin = User::factory()->create();
        $this->admin->assignRole('admin');
    }

    public function test_can_create_user_via_api(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->postJson('/api/users', [
            'email' => 'newuser@example.com',
            'phone' => '+1234567890',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'first_name' => 'Test',
            'last_name' => 'User',
            'gender' => 'male',
            'roles' => [$this->tenantRole->id],
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'id',
                    'email',
                    'phone',
                    'profile',
                    'roles',
                ],
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'newuser@example.com',
            'phone' => '+1234567890',
        ]);
    }

    public function test_validation_fails_for_invalid_data(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->postJson('/api/users', [
            'email' => 'invalid-email',
            'password' => 'short',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email', 'phone', 'password', 'first_name', 'last_name']);
    }

    public function test_cannot_create_user_with_duplicate_email(): void
    {
        Sanctum::actingAs($this->admin);

        $existingUser = User::factory()->create([
            'email' => 'existing@example.com',
        ]);

        $response = $this->postJson('/api/users', [
            'email' => 'existing@example.com',
            'phone' => '+9876543210',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'first_name' => 'Test',
            'last_name' => 'User',
            'gender' => 'male',
            'roles' => [$this->tenantRole->id],
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_can_update_user(): void
    {
        Sanctum::actingAs($this->admin);

        $user = User::factory()->create();
        $user->assignRole('tenant');

        $response = $this->putJson("/api/users/{$user->id}", [
            'email' => $user->email,
            'phone' => $user->phone,
            'first_name' => 'Updated',
            'last_name' => 'Name',
            'gender' => 'female',
            'roles' => [$this->tenantRole->id],
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('profiles', [
            'user_id' => $user->id,
            'first_name' => 'Updated',
            'last_name' => 'Name',
        ]);
    }

    public function test_can_get_user_list(): void
    {
        Sanctum::actingAs($this->admin);

        User::factory()->count(5)->create();

        $response = $this->getJson('/api/users');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => ['id', 'email', 'phone', 'profile'],
                ],
            ]);
    }

    public function test_unauthenticated_user_cannot_access_users(): void
    {
        $response = $this->getJson('/api/users');

        $response->assertStatus(401);
    }
}
