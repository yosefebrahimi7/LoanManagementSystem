<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LoanStatisticsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'total_loans' => $this->resource['total_loans'] ?? 0,
            'pending_loans' => $this->resource['pending_loans'] ?? 0,
            'approved_loans' => $this->resource['approved_loans'] ?? 0,
            'rejected_loans' => $this->resource['rejected_loans'] ?? 0,
            'active_loans' => $this->resource['active_loans'] ?? 0,
            'delinquent_loans' => $this->resource['delinquent_loans'] ?? 0,
            'paid_loans' => $this->resource['paid_loans'] ?? 0,
            'total_amount' => $this->resource['total_amount'] ?? 0,
            'formatted_total_amount' => number_format(($this->resource['total_amount'] ?? 0) / 100, 2),
            'monthly_loans' => $this->resource['monthly_loans'] ?? 0,
            'monthly_amount' => $this->resource['monthly_amount'] ?? 0,
            'formatted_monthly_amount' => number_format(($this->resource['monthly_amount'] ?? 0) / 100, 2),
        ];
    }

    /**
     * Get additional data that should be returned with the resource array.
     *
     * @return array<string, mixed>
     */
    public function with(Request $request): array
    {
        return [
            'meta' => [
                'generated_at' => now()->toISOString(),
                'currency' => 'IRR',
            ],
        ];
    }
}
