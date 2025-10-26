<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ZarinpalService
{
    private string $merchantId;
    private bool $sandbox;
    private string $baseUrl;

    public function __construct()
    {
        $this->merchantId = config('zarinpal.merchant_id');
        
        // Convert 'sandbox' or 'test' to valid UUID for sandbox mode
        if ($this->merchantId === 'sandbox' || $this->merchantId === 'test') {
            $this->merchantId = '00000000-0000-0000-0000-000000000000';
        }
        
        $this->sandbox = config('zarinpal.sandbox', true);
        $this->baseUrl = $this->sandbox 
            ? 'https://sandbox.zarinpal.com' 
            : 'https://payment.zarinpal.com';
    }

    /**
     * Initiate payment request
     *
     * @param int $amount Amount in Toman
     * @param string $description
     * @param string|null $callbackUrl
     * @param array|null $metadata
     * @return array
     */
    public function requestPayment(
        int $amount,
        string $description,
        ?string $callbackUrl = null,
        ?array $metadata = null
    ): array {
        try {
            $callbackUrl = $callbackUrl ?? config('zarinpal.callback_url');
            
            // Build request data
            $requestData = [
                'merchant_id' => $this->merchantId,
                'amount' => $amount,
                'callback_url' => $callbackUrl,
                'description' => $description,
            ];
            
            // Add metadata only if we have mobile or email
            $metadataArray = [];
            if (!empty($metadata['mobile'])) {
                $metadataArray['mobile'] = $metadata['mobile'];
            }
            if (!empty($metadata['email'])) {
                $metadataArray['email'] = $metadata['email'];
            }
            
            if (!empty($metadataArray)) {
                $requestData['metadata'] = $metadataArray;
            }

            // Send HTTP request
            $response = Http::post("{$this->baseUrl}/pg/v4/payment/request.json", $requestData);
            $data = $response->json();

            if (isset($data['data']['code']) && $data['data']['code'] == 100 && isset($data['data']['authority'])) {
                $authority = $data['data']['authority'];
                $paymentUrl = "{$this->baseUrl}/pg/StartPay/{$authority}";
                
                return [
                    'success' => true,
                    'authority' => $authority,
                    'payment_url' => $paymentUrl,
                ];
            }

            $errorMessage = $data['errors']['message'] ?? 'Unknown error';
            Log::error('Zarinpal payment request failed', [
                'code' => $data['data']['code'] ?? null,
                'amount' => $amount,
                'description' => $description,
                'error' => $errorMessage,
            ]);

            throw new \Exception('Payment request failed: ' . $errorMessage);

        } catch (\Exception $e) {
            Log::error('Zarinpal payment request exception', [
                'error' => $e->getMessage(),
                'amount' => $amount,
                'description' => $description,
            ]);

            throw $e;
        }
    }

    /**
     * Verify payment
     *
     * @param string $authority
     * @param int $amount
     * @return array
     */
    public function verifyPayment(string $authority, int $amount): array
    {
        try {
            $requestData = [
                'merchant_id' => $this->merchantId,
                'authority' => $authority,
                'amount' => $amount,
            ];

            // Send HTTP request
            $response = Http::post("{$this->baseUrl}/pg/v4/payment/verify.json", $requestData);
            $data = $response->json();

            if (isset($data['data']['code']) && $data['data']['code'] == 100) {
                return [
                    'success' => true,
                    'ref_id' => $data['data']['ref_id'] ?? null,
                    'card_hash' => $data['data']['card_hash'] ?? null,
                    'card_pan' => $data['data']['card_pan'] ?? null,
                ];
            }

            Log::error('Zarinpal payment verification failed', [
                'code' => $data['data']['code'] ?? null,
                'authority' => $authority,
                'amount' => $amount,
                'errors' => $data['errors'] ?? null,
            ]);

            return [
                'success' => false,
                'code' => $data['data']['code'] ?? null,
                'message' => $data['errors']['message'] ?? 'Verification failed',
            ];

        } catch (\Exception $e) {
            Log::error('Zarinpal payment verification exception', [
                'error' => $e->getMessage(),
                'authority' => $authority,
                'amount' => $amount,
            ]);

            throw $e;
        }
    }
}

