<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateProfileRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'unique:users,name,' . Auth::id(), 'regex:/^\w{2,20}$/'],
            'email' => ['required', 'unique:users,email,' . Auth::id(), 'email'],
            'avatar' => ['nullable', 'image'],
            'new_password' => [
                'nullable',
                'min:8',
                'regex:/[a-z]/',
                'regex:/[A-Z]/',
                'regex:/\d/',
                'different:old_password'
            ],
            'old_password' => ['required_with:new_password'],
            'password_confirmation' => ['same:new_password'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.regex' => 'The username must contain only latin letters and digits (2-20 chars)',
            'name.unique' => 'The username has already been taken',
            'email.unique' => 'The email has already been taken',
            'new_password' => 'The password must contain at least 1 uppercase letter, 1 lowercase letter, and 1 digit (8 or more chars)',
            'new_password.different' => 'The new password must be different from the old password',
            'old_password.required_with' => 'The old password is required to change the password',
        ];
    }
}
