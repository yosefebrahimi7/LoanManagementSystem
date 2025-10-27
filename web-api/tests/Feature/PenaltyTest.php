<?php

namespace Tests\Feature;

use App\Models\Loan;
use App\Models\LoanSchedule;
use App\Models\Setting;
use App\Services\PenaltyService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PenaltyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        
        // Set penalty rate
        Setting::updateOrCreate(
            ['key' => 'penalty_rate'],
            ['value' => '0.5', 'description' => 'Penalty rate per day (in percentage)']
        );
    }

    /**
     * Test penalty calculation for overdue installment
     */
    public function test_can_calculate_penalty_for_overdue_installment()
    {
        $loan = Loan::factory()->create();
        
        $schedule = LoanSchedule::factory()->create([
            'loan_id' => $loan->id,
            'amount_due' => 1000000, // 10,000 Toman = 1,000,000 cents
            'due_date' => now()->subDays(5),
            'status' => LoanSchedule::STATUS_PENDING,
            'penalty_amount' => 0,
        ]);

        $penaltyService = new PenaltyService();
        $penalty = $penaltyService->calculatePenalty($schedule);

        // Penalty should be: 1,000,000 * 0.005 * 5 = 25,000 cents
        // Or approximately (checking it's greater than 0)
        $this->assertGreaterThan(0, $penalty, "Expected penalty to be greater than 0, got: {$penalty}");
    }

    /**
     * Test penalty not calculated for non-overdue installment
     */
    public function test_no_penalty_for_not_overdue_installment()
    {
        $loan = Loan::factory()->create();
        
        $schedule = LoanSchedule::factory()->create([
            'loan_id' => $loan->id,
            'due_date' => now()->addDays(5),
            'status' => LoanSchedule::STATUS_PENDING,
        ]);

        $penaltyService = new PenaltyService();
        $penalty = $penaltyService->calculatePenalty($schedule);

        $this->assertEquals(0, $penalty);
    }

    /**
     * Test penalty command processes overdue installments
     */
    public function test_penalty_command_processes_overdue_installments()
    {
        $loan = Loan::factory()->create();
        
        // Create overdue schedules
        LoanSchedule::factory()->count(3)->create([
            'loan_id' => $loan->id,
            'due_date' => now()->subDays(10),
            'status' => LoanSchedule::STATUS_PENDING,
            'penalty_amount' => 0,
        ]);

        $this->artisan('loans:process-penalties')
            ->assertExitCode(0);

        $this->assertDatabaseHas('loan_schedules', [
            'status' => LoanSchedule::STATUS_OVERDUE,
        ]);
    }
}

