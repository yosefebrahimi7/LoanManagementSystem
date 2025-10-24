<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /**
     * Test user registration
     */
    public function test_user_can_register()
    {
        $userData = [
            'firstName' => 'John',
            'lastName' => 'Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->postJson('/api/auth/register', $userData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'user' => [
                        'id',
                        'firstName',
                        'lastName',
                        'email',
                        'isActive',
                        'createdAt',
                        'updatedAt',
                    ],
                    'token',
                    'refreshToken',
                ],
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'john@example.com',
            'first_name' => 'John',
            'last_name' => 'Doe',
        ]);
    }

    /**
     * Test user login
     */
    public function test_user_can_login()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $loginData = [
            'email' => 'test@example.com',
            'password' => 'password123',
        ];

        $response = $this->postJson('/api/auth/login', $loginData);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'user' => [
                        'id',
                        'firstName',
                        'lastName',
                        'email',
                        'isActive',
                        'createdAt',
                        'updatedAt',
                    ],
                    'token',
                    'refreshToken',
                ],
            ]);
    }

    /**
     * Test user logout
     */
    public function test_user_can_logout()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/auth/logout');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'خروج با موفقیت انجام شد',
            ]);
    }

    /**
     * Test get authenticated user
     */
    public function test_user_can_get_profile()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/auth/me');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'user' => [
                        'id',
                        'firstName',
                        'lastName',
                        'email',
                        'isActive',
                        'createdAt',
                        'updatedAt',
                    ],
                ],
            ]);
    }

    /**
     * Test token refresh
     */
    public function test_user_can_refresh_token()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/auth/refresh');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'token',
                    'refreshToken',
                ],
            ]);
    }

    /**
     * Test registration validation
     */
    public function test_registration_validation()
    {
        $response = $this->postJson('/api/auth/register', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['firstName', 'lastName', 'email', 'password']);
    }

    /**
     * Test login validation
     */
    public function test_login_validation()
    {
        $response = $this->postJson('/api/auth/login', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email', 'password']);
    }

    /**
     * Test duplicate email registration
     */
    public function test_cannot_register_with_duplicate_email()
    {
        User::factory()->create(['email' => 'test@example.com']);

        $userData = [
            'firstName' => 'John',
            'lastName' => 'Doe',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->postJson('/api/auth/register', $userData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /**
     * Test inactive user login
     */
    public function test_inactive_user_cannot_login()
    {
        $user = User::factory()->create([
            'email' => 'inactive@example.com',
            'password' => bcrypt('password123'),
            'is_active' => false,
        ]);

        $loginData = [
            'email' => 'inactive@example.com',
            'password' => 'password123',
        ];

        $response = $this->postJson('/api/auth/login', $loginData);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'حساب کاربری غیرفعال است',
            ]);
    }
}