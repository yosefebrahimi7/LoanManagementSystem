<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LoanResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'amount' => $this->amount,
            'formatted_amount' => number_format($this->amount / 100, 2),
            'term_months' => $this->term_months,
            'interest_rate' => $this->interest_rate,
            'monthly_payment' => $this->monthly_payment,
            'formatted_monthly_payment' => number_format($this->monthly_payment / 100, 2),
            'remaining_balance' => $this->remaining_balance,
            'formatted_remaining_balance' => number_format($this->remaining_balance / 100, 2),
            'status' => $this->status,
            'status_label' => $this->getStatusLabel(),
            'start_date' => $this->start_date,
            'approved_at' => $this->approved_at,
            'approved_by' => $this->approved_by,
            'rejection_reason' => $this->rejection_reason,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            
            // Relationships
            'user' => new UserResource($this->whenLoaded('user')),
            'approved_by_user' => new UserResource($this->whenLoaded('approvedBy')),
            'schedules' => LoanScheduleResource::collection($this->whenLoaded('schedules')),
            'payments' => LoanPaymentResource::collection($this->whenLoaded('payments')),
        ];
    }

    /**
     * Get status label in Persian
     */
    private function getStatusLabel(): string
    {
        return match($this->status) {
            'pending' => 'در انتظار تایید',
            'approved' => 'تایید شده',
            'rejected' => 'رد شده',
            'active' => 'فعال',
            'delinquent' => 'معوق',
            'paid' => 'پرداخت شده',
            default => $this->status,
        };
    }
}
