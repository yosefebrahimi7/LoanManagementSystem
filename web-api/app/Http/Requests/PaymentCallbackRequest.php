<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class PaymentCallbackRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Callback is public
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'Authority' => 'required|string|size:36',
            'Status' => 'nullable|string|in:OK,NOK',
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
            'Authority' => 'Authority',
            'Status' => 'Status',
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
            'Authority.required' => 'Authority الزامی است',
            'Authority.string' => 'Authority باید رشته باشد',
            'Authority.size' => 'Authority باید 36 کاراکتر باشد',
            
            'Status.string' => 'Status باید رشته باشد',
            'Status.in' => 'Status فقط می‌تواند OK یا NOK باشد',
        ];
    }

    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'message' => 'اطلاعات بازگشت از درگاه نامعتبر است',
                'errors' => $validator->errors()
            ], 422)
        );
    }
}

