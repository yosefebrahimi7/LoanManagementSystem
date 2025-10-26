<?php

namespace App\Services;

use App\Models\Loan;
use Illuminate\Support\Facades\Cache;

class LoanStatisticsService
{
    /**
     * Get comprehensive loan statistics
     */
    public function getStatistics(): array
    {
        return Cache::remember('loan.statistics', 300, function () {
            return [
                'total_loans' => Loan::count(),
                'pending_loans' => Loan::where('status', Loan::STATUS_PENDING)->count(),
                'approved_loans' => Loan::where('status', Loan::STATUS_APPROVED)->count(),
                'rejected_loans' => Loan::where('status', Loan::STATUS_REJECTED)->count(),
                'active_loans' => Loan::where('status', Loan::STATUS_ACTIVE)->count(),
                'delinquent_loans' => Loan::where('status', Loan::STATUS_DELINQUENT)->count(),
                'paid_loans' => Loan::where('status', Loan::STATUS_PAID)->count(),
                'total_amount' => Loan::where('status', Loan::STATUS_APPROVED)->sum('amount'),
                'monthly_loans' => Loan::whereMonth('created_at', now()->month)->count(),
                'monthly_amount' => Loan::whereMonth('created_at', now()->month)->sum('amount'),
            ];
        });
    }

    /**
     * Get statistics by status
     */
    public function getStatisticsByStatus(): array
    {
        $statuses = [
            Loan::STATUS_PENDING,
            Loan::STATUS_APPROVED,
            Loan::STATUS_REJECTED,
            Loan::STATUS_ACTIVE,
            Loan::STATUS_DELINQUENT,
            Loan::STATUS_PAID,
        ];

        $statistics = [];
        foreach ($statuses as $status) {
            $statistics[$status] = Loan::where('status', $status)->count();
        }

        return $statistics;
    }

    /**
     * Get monthly statistics
     */
    public function getMonthlyStatistics(): array
    {
        return [
            'current_month_loans' => Loan::whereMonth('created_at', now()->month)->count(),
            'current_month_amount' => Loan::whereMonth('created_at', now()->month)->sum('amount'),
            'last_month_loans' => Loan::whereMonth('created_at', now()->subMonth()->month)->count(),
            'last_month_amount' => Loan::whereMonth('created_at', now()->subMonth()->month)->sum('amount'),
        ];
    }

    /**
     * Clear statistics cache
     */
    public function clearCache(): void
    {
        Cache::forget('loan.statistics');
    }
}
