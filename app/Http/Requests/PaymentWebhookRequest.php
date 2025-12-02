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
            'idempotency_key' => 'required|string',
            'order_id'        => 'nullable|integer',
            'status'          => 'required|string|in:success,failure',
            'payload'         => 'nullable',
        ];
    }
}
