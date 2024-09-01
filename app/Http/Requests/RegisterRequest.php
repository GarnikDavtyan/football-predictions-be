<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'unique:users,name', 'regex:/^\w{2,20}$/'],
            'email' => ['required', 'unique:users,email', 'email'],
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
            'name.regex' => 'The username must contain only latin letters and digits (2-20 chars)',
            'password' => 'The password must contain at least 1 uppercase letter, 1 lowercase letter, and 1 digit (8 or more chars)',
        ];
    }
}
