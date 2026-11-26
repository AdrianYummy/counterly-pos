<?php

namespace App\Http\Requests;

use App\Models\User;
use App\Rules\UniqueCaseInsensitive;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;
use Illuminate\Validation\Rules\Password;

class StaffRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'username' => trim((string) $this->input('username')),
            'full_name' => preg_replace('/\s+/u', ' ', trim((string) $this->input('full_name'))) ?? '',
            'email' => Str::lower(trim((string) $this->input('email'))),
        ]);
    }

    public function rules(): array
    {
        /** @var User|null $staff */
        $staff = $this->route('staff');
        $passwordRule = $staff ? ['nullable'] : ['required'];

        return [
            'username' => ['required', 'string', 'min:3', 'max:50', 'regex:/^[A-Za-z0-9._-]+$/', Rule::unique('users', 'username')->ignore($staff)],
            'email' => ['required', 'email:rfc', 'max:150', Rule::unique('users', 'email')->ignore($staff)],
            'full_name' => ['required', 'string', 'max:100', new UniqueCaseInsensitive('users', 'full_name', $staff?->getKey(), 'A staff account with this full name already exists.')],
            'role' => ['required', Rule::in(['admin', 'cashier'])],
            'password' => [...$passwordRule, 'string', Password::min(10)->mixedCase()->numbers()->symbols(), 'max:72', 'confirmed'],
            'avatar' => ['nullable', File::image()->types(['jpg', 'jpeg', 'png', 'webp'])->max(4096), Rule::dimensions()->maxWidth(6000)->maxHeight(6000)],
        ];
    }
}
