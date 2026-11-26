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
        if ($this->has('items')) {
            return [
                'items' => ['required', 'array', 'min:1', 'max:100'],
                'items.*.product_id' => ['required', 'uuid', 'distinct:strict', 'exists:products,public_id'],
                'items.*.quantity' => ['required', 'integer', 'min:1', 'max:2147483647'],
                'customer_id' => ['nullable', 'uuid', 'exists:customers,public_id'],
                'email_receipt' => ['nullable', 'boolean'],
            ];
        }

        return [
            'product_id' => ['required', 'uuid', 'exists:products,public_id'],
            'customer_id' => ['nullable', 'uuid', 'exists:customers,public_id'],
            'quantity' => ['required', 'integer', 'min:1', 'max:2147483647'],
            'email_receipt' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'items.required' => 'Select at least one product before completing the sale.',
            'items.min' => 'Select at least one product before completing the sale.',
            'items.*.product_id.distinct' => 'Each product may only appear once in the cart.',
            'items.*.quantity.min' => 'Every selected product must have a quantity of at least one.',
        ];
    }
}
