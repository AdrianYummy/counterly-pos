<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
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
            'full_name' => trim((string) $this->input('full_name')),
        ]);
    }

    public function rules(): array
    {
        /** @var User|null $staff */
        $staff = $this->route('staff');
        $passwordRule = $staff ? ['nullable'] : ['required'];

        return [
            'username' => ['required', 'string', 'min:3', 'max:50', 'regex:/^[A-Za-z0-9._-]+$/', Rule::unique('users', 'username')->ignore($staff)],
            'full_name' => ['required', 'string', 'max:100'],
            'password' => [...$passwordRule, 'string', Password::min(8), 'max:72', 'confirmed'],
            'avatar' => ['nullable', File::image()->types(['jpg', 'jpeg', 'png', 'webp'])->max(4096), Rule::dimensions()->maxWidth(6000)->maxHeight(6000)],
        ];
    }
}
