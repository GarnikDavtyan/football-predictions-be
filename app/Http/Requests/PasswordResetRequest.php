<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PasswordResetRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'token' => 'required',
            'email' => 'required|email',
            'password' => [
                'required',
                'min:8',
                'regex:/[a-z]/',
                'regex:/[A-Z]/',
                'regex:/\d/',
            ],
            'password_confirmation' => ['same:password'],
        ];
    }

    public function messages(): array
    {
        return [
            'password' => 'The password must contain at least 1 uppercase letter, 1 lowercase letter, and 1 digit (8 or more chars)',
        ];
    }
}
