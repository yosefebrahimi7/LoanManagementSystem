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
            'loan_schedule_id' => $this->loan_schedule_id,
            'user_id' => $this->user_id,
            'amount' => $this->amount,
            'formatted_amount' => number_format($this->amount) . ' تومان',
            'payment_method' => $this->payment_method,
            'payment_method_label' => $this->getPaymentMethodLabel(),
            'status' => $this->status,
            'status_label' => $this->getStatusLabel(),
            'gateway_reference' => $this->gateway_reference,
            'gateway_response' => $this->gateway_response,
            'notes' => $this->notes,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
            
            // Relationships
            'loan' => new LoanResource($this->whenLoaded('loan')),
            'user' => new UserResource($this->whenLoaded('user')),
            'schedule' => $this->whenLoaded('schedule', function () {
                return [
                    'id' => $this->schedule->id,
                    'installment_number' => $this->schedule->installment_number,
                    'amount_due' => $this->schedule->amount_due,
                    'paid_amount' => $this->schedule->paid_amount,
                    'status' => $this->schedule->status,
                ];
            }),
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
            'refunded' => 'بازگردانده شده',
            default => $this->status,
        };
    }

    /**
     * Get payment method label in Persian
     */
    private function getPaymentMethodLabel(): string
    {
        return match($this->payment_method) {
            'zarinpal' => 'زرین‌پال',
            'manual' => 'دستی',
            default => $this->payment_method,
        };
    }
}
