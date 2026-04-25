<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'roleId' => 'nullable|integer|exists:roles,id',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'login' => 'required|string|max:255|unique:users',
            'password' => ['required', 'confirmed', 'min:10', 'regex:/^(?=.*[A-Za-z])(?=.*\d).+$/'], //https://regex101.com/
        ];
    }
}
