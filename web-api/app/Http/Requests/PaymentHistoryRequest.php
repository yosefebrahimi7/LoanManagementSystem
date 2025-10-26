<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class PaymentHistoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return \Illuminate\Support\Facades\Gate::allows('viewHistory', \App\Models\LoanPayment::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'per_page' => 'sometimes|integer|min:1|max:100',
            'status' => 'sometimes|string|in:pending,completed,failed,refunded',
            'loan_id' => 'sometimes|integer|exists:loans,id',
            'from_date' => 'sometimes|date',
            'to_date' => 'sometimes|date|after_or_equal:from_date',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'per_page' => 'تعداد نمایش',
            'status' => 'وضعیت',
            'loan_id' => 'شناسه وام',
            'from_date' => 'تاریخ شروع',
            'to_date' => 'تاریخ پایان',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'per_page.integer' => 'تعداد نمایش باید عدد باشد',
            'per_page.min' => 'حداقل تعداد نمایش 1 است',
            'per_page.max' => 'حداکثر تعداد نمایش 100 است',
            
            'status.string' => 'وضعیت باید رشته باشد',
            'status.in' => 'وضعیت نامعتبر است. فقط: pending, completed, failed, refunded',
            
            'loan_id.integer' => 'شناسه وام باید عدد باشد',
            'loan_id.exists' => 'وام انتخابی یافت نشد',
            
            'from_date.date' => 'تاریخ شروع نامعتبر است',
            'to_date.date' => 'تاریخ پایان نامعتبر است',
            'to_date.after_or_equal' => 'تاریخ پایان باید بعد از تاریخ شروع باشد',
        ];
    }

    /**
     * Handle a failed authorization.
     */
    protected function failedAuthorization()
    {
        throw new HttpResponseException(
            response()->json([
                'message' => 'شما اجازه مشاهده تاریخچه پرداخت را ندارید',
                'errors' => []
            ], 403)
        );
    }

    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'message' => 'اطلاعات وارد شده نامعتبر است',
                'errors' => $validator->errors()
            ], 422)
        );
    }
}

