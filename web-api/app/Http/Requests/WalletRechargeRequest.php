<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class WalletRechargeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization handled in controller
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'amount' => ['required', 'integer', 'min:10000', 'max:2000000000'], // Minimum 10,000 Tomans, Maximum 2,000,000,000 Tomans (Zarinpal limit: 200,000,000,000 rials = 20,000,000,000 tomans)
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'amount.required' => 'مبلغ شارژ الزامی است',
            'amount.integer' => 'مبلغ باید یک عدد صحیح باشد',
            'amount.min' => 'حداقل مبلغ شارژ ۱۰,۰۰۰ تومان است',
            'amount.max' => 'حداکثر مبلغ شارژ ۲,۰۰۰,۰۰۰,۰۰۰ تومان است',
        ];
    }
}
