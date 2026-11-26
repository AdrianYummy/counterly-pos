<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SaleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_id' => ['required', 'uuid', 'exists:products,public_id'],
            'customer_id' => ['nullable', 'uuid', 'exists:customers,public_id'],
            'quantity' => ['required', 'integer', 'min:1', 'max:2147483647'],
            'email_receipt' => ['nullable', 'boolean'],
        ];
    }
}
