<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PaymentWebhookRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'transaction_reference' => 'required|string',
            'order_uuid'            => 'nullable|string|exists:orders,uuid',
            'status'                => 'required|string|in:success,failure',
            'payload'               => 'nullable',
        ];
    }
}
