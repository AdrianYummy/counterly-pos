<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;

class ProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['name' => trim((string) $this->input('name'))]);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'price' => ['required', 'regex:/^\d{1,8}(?:\.\d{1,2})?$/', 'decimal:0,2', 'gt:0', 'max:99999999.99'],
            'stock_quantity' => ['required', 'integer', 'min:0', 'max:2147483647'],
            'original_stock' => [$this->isMethod('put') || $this->isMethod('patch') ? 'required' : 'nullable', 'integer', 'min:0'],
            'image' => ['nullable', File::image()->types(['jpg', 'jpeg', 'png', 'webp'])->max(4096), Rule::dimensions()->maxWidth(6000)->maxHeight(6000)],
        ];
    }
}
