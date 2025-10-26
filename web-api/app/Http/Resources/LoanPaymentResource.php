<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LoanPaymentResource extends JsonResource
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
            'user_id' => $this->user_id,
            'amount' => $this->amount,
            'formatted_amount' => number_format($this->amount / 100, 2),
            'method' => $this->method,
            'status' => $this->status,
            'status_label' => $this->getStatusLabel(),
            'gateway_reference' => $this->gateway_reference,
            'paid_at' => $this->paid_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            
            // Relationships
            'loan' => new LoanResource($this->whenLoaded('loan')),
            'user' => new UserResource($this->whenLoaded('user')),
        ];
    }

    /**
     * Get status label in Persian
     */
    private function getStatusLabel(): string
    {
        return match($this->status) {
            'pending' => 'در انتظار پرداخت',
            'completed' => 'تکمیل شده',
            'failed' => 'ناموفق',
            'cancelled' => 'لغو شده',
            default => $this->status,
        };
    }
}
