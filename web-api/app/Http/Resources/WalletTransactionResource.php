<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WalletTransactionResource extends JsonResource
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
            'wallet_id' => $this->wallet_id,
            'type' => $this->type,
            'type_label' => $this->getTypeLabel(),
            'amount' => $this->amount,
            'formatted_amount' => number_format($this->amount / 100, 2),
            'balance_after' => $this->balance_after,
            'formatted_balance_after' => number_format($this->balance_after / 100, 2),
            'meta' => $this->meta,
            'description' => $this->description,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            
            // Relationships
            'wallet' => new WalletResource($this->whenLoaded('wallet')),
        ];
    }

    /**
     * Get type label in Persian
     */
    private function getTypeLabel(): string
    {
        return match($this->type) {
            'credit' => 'واریز',
            'debit' => 'برداشت',
            default => $this->type,
        };
    }
}
