<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreBookingRequest extends FormRequest
{
    /**
     * Booking is open to everyone: the app has no authentication.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'slot_id' => ['required', 'integer', 'exists:slots,id'],
            'customer_name' => ['required', 'string', 'max:100'],
            'customer_email' => ['required', 'string', 'email', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'slot_id.required' => 'Выберите слот.',
            'slot_id.integer' => 'Некорректный идентификатор слота.',
            'slot_id.exists' => 'Такого слота не существует.',
            'customer_name.required' => 'Укажите имя.',
            'customer_name.max' => 'Имя должно быть не длиннее 100 символов.',
            'customer_email.required' => 'Укажите email.',
            'customer_email.email' => 'Укажите корректный email.',
            'customer_email.max' => 'Email должен быть не длиннее 255 символов.',
        ];
    }
}
