<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreHoldRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_uuid' => 'required|string|exists:products,uuid',
            'qty'          => 'required|integer|min:1',
        ];
    }
}
