<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class LoanRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'amount' => 'required|integer|min:1000000|max:100000000', // 1M to 100M IRR
            'term_months' => 'required|integer|min:3|max:36',
            'interest_rate' => 'nullable|numeric|min:0|max:50',
            'start_date' => 'required|date|after_or_equal:today',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'amount.required' => 'مبلغ وام الزامی است',
            'amount.integer' => 'مبلغ وام باید عدد صحیح باشد',
            'amount.min' => 'حداقل مبلغ وام 1,000,000 تومان است',
            'amount.max' => 'حداکثر مبلغ وام 100,000,000 تومان است',
            'term_months.required' => 'مدت وام الزامی است',
            'term_months.integer' => 'مدت وام باید عدد صحیح باشد',
            'term_months.min' => 'حداقل مدت وام 3 ماه است',
            'term_months.max' => 'حداکثر مدت وام 36 ماه است',
            'interest_rate.numeric' => 'نرخ بهره باید عدد باشد',
            'interest_rate.min' => 'نرخ بهره نمی‌تواند منفی باشد',
            'interest_rate.max' => 'نرخ بهره نمی‌تواند بیش از 50% باشد',
            'start_date.required' => 'تاریخ شروع الزامی است',
            'start_date.date' => 'فرمت تاریخ نامعتبر است',
            'start_date.after_or_equal' => 'تاریخ شروع باید امروز یا بعد از امروز باشد',
        ];
    }

    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'اطلاعات وارد شده نامعتبر است',
                'errors' => $validator->errors()
            ], 422)
        );
    }
}
