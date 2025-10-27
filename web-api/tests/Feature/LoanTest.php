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
}

