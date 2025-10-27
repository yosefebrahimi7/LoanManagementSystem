<?php

namespace Tests\Feature;

use App\Models\Loan;
use App\Models\LoanSchedule;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoanTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    /**
     * Test create loan request
     */
    public function test_user_can_create_loan_request()
    {
        $user = User::factory()->create();

        $token = $user->createToken('test')->plainTextToken;

        $loanData = [
            'amount' => 10000000, // 100,000 Toman
            'term_months' => 12,
            'interest_rate' => 14.5,
            'start_date' => now()->addMonth()->format('Y-m-d'),
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/loans', $loanData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'amount',
                    'term_months',
                    'status',
                ],
            ]);

        $this->assertDatabaseHas('loans', [
            'user_id' => $user->id,
            'amount' => 10000000,
            'status' => Loan::STATUS_PENDING,
        ]);
    }

    /**
     * Test admin can approve loan
     */
    public function test_admin_can_approve_loan()
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();
        $loan = Loan::factory()->create([
            'user_id' => $user->id,
            'status' => Loan::STATUS_PENDING,
        ]);

        $token = $admin->createToken('test')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson("/api/admin/loans/{$loan->id}/approve", [
            'action' => 'approve',
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('loans', [
            'id' => $loan->id,
            'status' => Loan::STATUS_APPROVED,
        ]);

        // Check that payment schedule is created
        $this->assertDatabaseHas('loan_schedules', [
            'loan_id' => $loan->id,
        ]);
    }

    /**
     * Test user can get their loans
     */
    public function test_user_can_get_their_loans()
    {
        $user = User::factory()->create();
        $loans = Loan::factory()->count(3)->create([
            'user_id' => $user->id,
        ]);

        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/loans');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => [
                        'id',
                        'amount',
                        'status',
                    ],
                ],
            ]);
    }

    /**
     * Test admin can reject loan
     */
    public function test_admin_can_reject_loan()
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();
        $loan = Loan::factory()->create([
            'user_id' => $user->id,
            'status' => Loan::STATUS_PENDING,
        ]);

        $token = $admin->createToken('test')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson("/api/admin/loans/{$loan->id}/approve", [
            'action' => 'reject',
            'rejection_reason' => 'Insufficient credit',
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('loans', [
            'id' => $loan->id,
            'status' => Loan::STATUS_REJECTED,
        ]);
    }

    /**
     * Test loan schedule is generated after approval
     */
    public function test_loan_schedule_is_generated_after_approval()
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();
        $loan = Loan::factory()->create([
            'user_id' => $user->id,
            'amount' => 12000000, // 120,000 Toman
            'term_months' => 12,
            'status' => Loan::STATUS_PENDING,
        ]);

        // Get initial count
        $initialCount = \App\Models\LoanSchedule::count();

        $token = $admin->createToken('test')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson("/api/admin/loans/{$loan->id}/approve", [
            'action' => 'approve',
        ]);

        $response->assertStatus(200);

        // Check that 12 schedules are created (12 months)
        $this->assertDatabaseCount('loan_schedules', $initialCount + 12);
        
        $this->assertDatabaseHas('loan_schedules', [
            'loan_id' => $loan->id,
            'installment_number' => 1,
        ]);
    }

    /**
     * Test user cannot access another user's loan details
     */
    public function test_user_cannot_access_another_users_loan()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $loan = Loan::factory()->create([
            'user_id' => $user1->id,
        ]);

        $token = $user2->createToken('test')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson("/api/loans/{$loan->id}");

        // Check that user gets 403 or 404 (depending on implementation)
        // 404 is acceptable as it means resource not found for that user
        $this->assertTrue(in_array($response->status(), [403, 404]));
    }
}

