<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LoanScheduleResource extends JsonResource
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
            'loan_id' => $this->loan_id,
            'installment_number' => $this->installment_number,
            'amount_due' => $this->amount_due,
            'formatted_amount_due' => number_format($this->amount_due / 100, 2),
            'principal_amount' => $this->principal_amount,
            'formatted_principal_amount' => number_format($this->principal_amount / 100, 2),
            'interest_amount' => $this->interest_amount,
            'formatted_interest_amount' => number_format($this->interest_amount / 100, 2),
            'due_date' => $this->due_date,
            'status' => $this->status,
            'status_label' => $this->getStatusLabel(),
            'paid_at' => $this->paid_at,
            'penalty_amount' => $this->penalty_amount,
            'formatted_penalty_amount' => number_format($this->penalty_amount / 100, 2),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            
            // Relationships
            'loan' => new LoanResource($this->whenLoaded('loan')),
        ];
    }

    /**
     * Get status label in Persian
     */
    private function getStatusLabel(): string
    {
        return match($this->status) {
            'pending' => 'در انتظار پرداخت',
            'paid' => 'پرداخت شده',
            'overdue' => 'معوق',
            default => $this->status,
        };
    }
}
