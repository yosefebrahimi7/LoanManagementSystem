<?php

namespace App\Http\Requests;

use App\Models\Loan;
use App\Policies\LoanPolicy;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class PaymentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', Loan::findOrFail($this->route('loan')->id));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'schedule_id' => 'required|integer|exists:loan_schedules,id',
            'amount' => 'nullable|integer|min:1000|max:10000000000',
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
            'schedule_id' => 'شناسه قسط',
            'amount' => 'مبلغ پرداخت',
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
            'schedule_id.required' => 'شناسه قسط الزامی است',
            'schedule_id.integer' => 'شناسه قسط باید عدد باشد',
            'schedule_id.exists' => 'قسط انتخابی یافت نشد',
            
            'amount.nullable' => 'مبلغ می‌تواند خالی باشد',
            'amount.integer' => 'مبلغ باید عدد باشد',
            'amount.min' => 'حداقل مبلغ پرداخت 1,000 تومان است',
            'amount.max' => 'حداکثر مبلغ پرداخت 100,000,000,000 تومان است',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator(\Illuminate\Contracts\Validation\Validator $validator): void
    {
        $validator->after(function ($validator) {
            $loan = $this->route('loan');
            $scheduleId = $this->schedule_id;
            
            // Check if schedule belongs to the loan
            if ($scheduleId) {
                $scheduleExists = \App\Models\LoanSchedule::where('id', $scheduleId)
                    ->where('loan_id', $loan->id)
                    ->exists();
                    
                if (!$scheduleExists) {
                    $validator->errors()->add('schedule_id', 'این قسط متعلق به این وام نیست');
                }
            }
            
            // Check if amount exceeds remaining amount
            if ($this->amount && $scheduleId) {
                $schedule = \App\Models\LoanSchedule::find($scheduleId);
                if ($schedule && $this->amount > $schedule->remaining_amount) {
                    $validator->errors()->add('amount', 'مبلغ ورودی بیش از باقیمانده قسط است');
                }
            }
        });
    }

    /**
     * Handle a failed authorization.
     */
    protected function failedAuthorization()
    {
        throw new HttpResponseException(
            response()->json([
                'message' => 'شما اجازه درخواست پرداخت برای این وام را ندارید',
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

