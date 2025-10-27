<?php

namespace App\Services;

use App\Models\LoanSchedule;
use App\Models\Setting;
use Illuminate\Support\Facades\DB;

class PenaltyService
{
    /**
     * Calculate penalty for an overdue installment
     */
    public function calculatePenalty(LoanSchedule $schedule): int
    {
        // Check if installment is overdue
        if ($schedule->due_date >= now()) {
            return 0;
        }
        
        if ($schedule->status === LoanSchedule::STATUS_PAID) {
            return 0;
        }

        // Get penalty rate from settings (default 0.5% per day)
        $penaltyRate = $this->getPenaltyRate();
        
        // Calculate days overdue (positive value)
        $dueDate = \Carbon\Carbon::parse($schedule->due_date);
        $now = \Carbon\Carbon::now();
        
        // Calculate the difference in days between due date and now
        // If due date is in the past, the result will be negative, so we need abs()
        $daysOverdue = $now->gt($dueDate) ? abs($now->diffInDays($dueDate)) : 0;
        
        if ($daysOverdue <= 0) {
            return 0;
        }

        // Calculate penalty amount: amount_due * penalty_rate * days
        $penaltyAmount = intval($schedule->amount_due * $penaltyRate * $daysOverdue);
        
        return max(0, $penaltyAmount);
    }

    /**
     * Process all overdue installments and update penalty
     */
    public function processOverdueInstallments(): array
    {
        $overdueSchedules = LoanSchedule::where('due_date', '<', now())
            ->where('status', '!=', LoanSchedule::STATUS_PAID)
            ->get();

        $processed = 0;
        $totalPenalty = 0;

        foreach ($overdueSchedules as $schedule) {
            DB::transaction(function () use ($schedule, &$processed, &$totalPenalty) {
                // Mark as overdue if not already
                if ($schedule->status !== LoanSchedule::STATUS_OVERDUE) {
                    $schedule->update(['status' => LoanSchedule::STATUS_OVERDUE]);
                }

                // Calculate and update penalty
                $penaltyAmount = $this->calculatePenalty($schedule);
                
                if ($penaltyAmount > $schedule->penalty_amount) {
                    $schedule->update(['penalty_amount' => $penaltyAmount]);
                    $totalPenalty += $penaltyAmount;
                    $processed++;
                }
            });
        }

        return [
            'processed_count' => $processed,
            'total_penalty' => $totalPenalty,
            'overdue_installments' => $overdueSchedules->count(),
        ];
    }

    /**
     * Get penalty rate from settings
     */
    private function getPenaltyRate(): float
    {
        $setting = Setting::where('key', 'penalty_rate')->first();
        return $setting ? floatval($setting->value) / 100 : 0.005; // Default 0.5% per day
    }
}

