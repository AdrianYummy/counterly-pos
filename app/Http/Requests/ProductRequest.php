<?php

namespace App\Http\Requests;

use App\Models\Product;
use App\Rules\UniqueCaseInsensitive;
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
        $this->merge(['name' => preg_replace('/\s+/u', ' ', trim((string) $this->input('name'))) ?? '']);
    }

    public function rules(): array
    {
        /** @var Product|null $product */
        $product = $this->route('product');

        return [
            'name' => ['required', 'string', 'max:100', new UniqueCaseInsensitive('products', 'name', $product?->getKey(), 'A product with this name already exists.')],
            'price' => ['required', 'regex:/^\d{1,8}(?:\.\d{1,2})?$/', 'decimal:0,2', 'gt:0', 'max:99999999.99'],
            'stock_quantity' => ['required', 'integer', 'min:0', 'max:2147483647'],
            'original_stock' => [$this->isMethod('put') || $this->isMethod('patch') ? 'required' : 'nullable', 'integer', 'min:0'],
            'image' => ['nullable', File::image()->types(['jpg', 'jpeg', 'png', 'webp'])->max(4096), Rule::dimensions()->maxWidth(6000)->maxHeight(6000)],
        ];
    }
}
