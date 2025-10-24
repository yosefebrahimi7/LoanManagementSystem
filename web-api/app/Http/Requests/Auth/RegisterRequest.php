<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class RegisterRequest extends FormRequest
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
            'firstName' => 'required|string|max:255|min:2',
            'lastName' => 'required|string|max:255|min:2',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
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
            'firstName.required' => 'نام الزامی است',
            'firstName.min' => 'نام باید حداقل 2 کاراکتر باشد',
            'firstName.max' => 'نام نمی‌تواند بیش از 255 کاراکتر باشد',
            'lastName.required' => 'نام خانوادگی الزامی است',
            'lastName.min' => 'نام خانوادگی باید حداقل 2 کاراکتر باشد',
            'lastName.max' => 'نام خانوادگی نمی‌تواند بیش از 255 کاراکتر باشد',
            'email.required' => 'ایمیل الزامی است',
            'email.email' => 'فرمت ایمیل نامعتبر است',
            'email.max' => 'ایمیل نمی‌تواند بیش از 255 کاراکتر باشد',
            'email.unique' => 'این ایمیل قبلا ثبت شده است',
            'password.required' => 'رمز عبور الزامی است',
            'password.min' => 'رمز عبور باید حداقل 8 کاراکتر باشد',
            'password.confirmed' => 'تکرار رمز عبور مطابقت ندارد',
        ];
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