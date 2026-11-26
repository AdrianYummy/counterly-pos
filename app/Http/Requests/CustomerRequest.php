<?php

namespace App\Http\Requests;

use App\Models\Customer;
use App\Rules\UniqueCaseInsensitive;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class CustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'full_name' => preg_replace('/\s+/u', ' ', trim((string) $this->input('full_name'))) ?? '',
            'email' => Str::lower(trim((string) $this->input('email'))),
            'phone' => filled($this->input('phone')) ? trim((string) $this->input('phone')) : null,
        ]);
    }

    public function rules(): array
    {
        /** @var Customer|null $customer */
        $customer = $this->route('customer');

        return [
            'full_name' => ['required', 'string', 'max:100', new UniqueCaseInsensitive('customers', 'full_name', $customer?->getKey(), 'A customer with this full name already exists.')],
            'email' => ['required', 'email:rfc', 'max:100', new UniqueCaseInsensitive('customers', 'email', $customer?->getKey(), 'A customer with this email address already exists.')],
            'phone' => ['nullable', 'string', 'max:20', 'regex:/^[0-9+()\-\.\s]+$/'],
        ];
    }
}
